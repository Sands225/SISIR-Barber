<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Service;
use App\Models\Setting;
use App\Models\BarberSchedule;
use App\Models\User;
use App\Jobs\SlotRecoveryBroadcastJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * GET /settings
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Fetch settings and set default values if they don't exist
        $settings = [
            'shop_name'        => Setting::get('shop_name', 'SISIR Barber'),
            'shop_address'     => Setting::get('shop_address', 'Jl. Contoh No.1, Depok'),
            'whatsapp_number'  => Setting::get('whatsapp_number', '628123456789'),
            'chairs_count'     => Setting::get('chairs_count', 3),
            'slot_duration'    => Setting::get('slot_duration', 30),
            'opening_time'     => Setting::get('opening_time', '09:00'),
            'closing_time'     => Setting::get('closing_time', '21:00'),
            'dp_amount'        => Setting::get('dp_amount', 50),
        ];

        $barbers = Barber::with('user')->get();
        $services = Service::all();

        return view('sisir.settings', compact('user', 'settings', 'barbers', 'services'));
    }

    /**
     * POST /settings/operational
     */
    public function updateOperational(Request $request): RedirectResponse
    {
        $request->validate([
            'shop_name'       => ['required', 'string', 'max:255'],
            'shop_address'    => ['required', 'string', 'max:500'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'chairs_count'    => ['required', 'integer', 'min:1', 'max:50'],
            'slot_duration'   => ['required', 'integer', 'min:30', 'max:120'],
            'opening_time'    => ['required', 'date_format:H:i'],
            'closing_time'    => ['required', 'date_format:H:i', 'after:opening_time'],
            'dp_amount'       => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        // Validation for kelipatan 10
        if ($request->slot_duration % 10 !== 0) {
            return back()->withErrors(['slot_duration' => 'Jeda waktu minimal pengerjaan harus kelipatan 10 menit.'])->withInput();
        }

        Setting::set('shop_name', $request->shop_name);
        Setting::set('shop_address', $request->shop_address);
        Setting::set('whatsapp_number', $request->whatsapp_number);
        Setting::set('chairs_count', $request->chairs_count);
        Setting::set('slot_duration', $request->slot_duration);
        Setting::set('opening_time', $request->opening_time);
        Setting::set('closing_time', $request->closing_time);
        Setting::set('dp_amount', $request->dp_amount);

        // Synchronize all barber schedules with the new shop opening/closing hours
        BarberSchedule::query()->update([
            'open_time'  => $request->opening_time,
            'close_time' => $request->closing_time,
        ]);

        return back()->with('success', 'Pengaturan operasional berhasil diperbarui.');
    }

    /**
     * POST /settings/barbers
     */
    public function saveBarber(Request $request): RedirectResponse
    {
        $request->validate([
            'nickname'  => ['required', 'string', 'max:100'],
            'bio'       => ['nullable', 'string', 'max:500'],
        ]);

        if ($request->filled('id')) {
            // Edit Existing
            $barber = Barber::findOrFail($request->id);
            $barber->update([
                'nickname'  => $request->nickname,
                'bio'       => $request->bio,
                'is_active' => $request->has('is_active'),
            ]);

            if ($barber->user) {
                $barber->user->update(['name' => $request->nickname]);
            }
            
            return back()->with('success', 'Data kapster berhasil diperbarui.');
        } else {
            // Create New
            $email = strtolower(str_replace(' ', '', $request->nickname)) . '@sisir.barber';
            if (User::where('email', $email)->exists()) {
                $email = strtolower(str_replace(' ', '', $request->nickname)) . rand(10, 99) . '@sisir.barber';
            }

            $user = User::create([
                'name'     => $request->nickname,
                'email'    => $email,
                'password' => Hash::make('password'),
            ]);

            $barber = Barber::create([
                'user_id'           => $user->id,
                'nickname'          => $request->nickname,
                'bio'               => $request->bio,
                'capacity_per_slot' => 1,
                'is_active'         => true,
            ]);

            // Seed default daily schedules
            $open = Setting::get('opening_time', '09:00');
            $close = Setting::get('closing_time', '21:00');
            for ($i = 0; $i < 7; $i++) {
                BarberSchedule::create([
                    'barber_id'   => $barber->id,
                    'day_of_week' => $i,
                    'open_time'   => $open,
                    'close_time'  => $close,
                    'is_active'   => true,
                ]);
            }

            return back()->with('success', 'Kapster baru berhasil ditambahkan.');
        }
    }

    /**
     * POST /settings/barbers/{id}/delete
     */
    public function deleteBarber(int $id): RedirectResponse
    {
        $barber = Barber::findOrFail($id);
        $barber->delete(); // Soft delete

        return back()->with('success', 'Kapster berhasil dihapus.');
    }

    /**
     * POST /settings/services
     */
    public function saveService(Request $request): RedirectResponse
    {
        $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:500'],
            'duration_minutes' => ['required', 'integer', 'min:30'],
            'price'            => ['required', 'integer', 'min:0'],
        ]);

        if ($request->duration_minutes % 10 !== 0) {
            return back()->withErrors(['duration_minutes' => 'Durasi layanan harus kelipatan 10 menit.'])->withInput();
        }

        if ($request->filled('id')) {
            // Edit Existing
            $service = Service::findOrFail($request->id);
            $service->update([
                'name'             => $request->name,
                'description'      => $request->description,
                'duration_minutes' => $request->duration_minutes,
                'price'            => $request->price,
                'is_active'        => $request->has('is_active'),
            ]);

            return back()->with('success', 'Data layanan berhasil diperbarui.');
        } else {
            // Create New
            Service::create([
                'name'             => $request->name,
                'description'      => $request->description,
                'duration_minutes' => $request->duration_minutes,
                'price'            => $request->price,
                'is_active'        => true,
            ]);

            return back()->with('success', 'Layanan baru berhasil ditambahkan.');
        }
    }

    /**
     * POST /settings/services/{id}/delete
     */
    public function deleteService(int $id): RedirectResponse
    {
        $service = Service::findOrFail($id);
        $service->delete(); // Soft delete

        return back()->with('success', 'Layanan berhasil dihapus.');
    }

    /**
     * POST /settings/promo/send
     */
    public function sendPromo(Request $request): JsonResponse
    {
        $request->validate([
            'discount_amount' => ['required', 'numeric', 'min:1000', 'max:500000'],
        ]);

        $discountPct = 15; // Fixed 15% for demo

        SlotRecoveryBroadcastJob::dispatch(
            now()->addHour()->toIso8601String(),
            $discountPct
        )->onQueue('broadcasts');

        return response()->json([
            'success' => true,
            'message' => 'Promo broadcast dijadwalkan! Pesan akan dikirim ke semua pelanggan waitlist.',
        ]);
    }
}
