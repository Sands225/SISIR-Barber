<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Services\CapacityEngine;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private CapacityEngine  $capacity,
        private MidtransService $midtrans,
    ) {}

    /**
     * GET /booking
     * Full booking list view for the customer.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Build query
        $query = Booking::with(['barber.user', 'service', 'customer'])
            ->orderByDesc('scheduled_at');

        // Status filter
        $filterStatus = $request->get('status', 'Semua');

        if ($filterStatus !== 'Semua') {
            $statusMap = [
                'Sudah DP' => [BookingStatus::BOOKED->value, BookingStatus::CONFIRMED->value],
                'Selesai'  => [BookingStatus::COMPLETED->value],
                'Batal'    => [BookingStatus::CANCELLED_BY_SYSTEM->value, BookingStatus::NO_SHOW->value],
                'Menunggu' => [BookingStatus::TEMP_LOCKED->value],
            ];

            if (isset($statusMap[$filterStatus])) {
                $query->whereIn('status', $statusMap[$filterStatus]);
            }
        }

        $bookings = $query->paginate(20);

        return view('sisir.booking', compact('bookings', 'user', 'filterStatus'));
    }

    /**
     * GET /booking/{id}
     * Detail modal data (JSON) for a specific booking.
     */
    public function show(int $id): JsonResponse
    {
        $booking = Booking::with(['barber.user', 'service', 'customer'])->findOrFail($id);

        return response()->json([
            'id'             => $booking->id,
            'customer_name'  => $booking->customer->name,
            'service_name'   => $booking->service->name,
            'barber_name'    => $booking->barber->displayName(),
            'scheduled_at'   => $booking->scheduledAtFormatted(),
            'status'         => $booking->status->value,
            'status_label'   => $booking->status->label(),
            'status_color'   => $booking->status->color(),
            'dp_amount'      => number_format($booking->dp_amount, 0, ',', '.'),
            'qr_code_url'    => $booking->midtrans_qr_code_url,
            'midtrans_order' => $booking->midtrans_order_id,
        ]);
    }

    /**
     * GET /booking/slots
     * AJAX: available time slots for a barber on a date.
     */
    public function slots(Request $request): JsonResponse
    {
        $request->validate([
            'barber_id' => ['required', 'integer'],
            'date'      => ['required', 'date'],
        ]);

        $date  = \Illuminate\Support\Carbon::parse($request->date);
        $slots = $this->capacity->getAvailableSlots((int) $request->barber_id, $date);

        return response()->json(['slots' => $slots]);
    }

    /**
     * GET /booking/create
     * Customer booking form.
     */
    public function create(): View
    {
        $services = Service::where('is_active', true)->get();
        $barbers  = Barber::where('is_active', true)->with('user')->get();

        return view('sisir.booking-create', compact('services', 'barbers'));
    }

    /**
     * POST /booking
     * Submit a new booking and generate QRIS DP payment.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'barber_id'    => ['required', 'integer', 'exists:barbers,id'],
            'service_id'   => ['required', 'integer', 'exists:services,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'name'         => ['required', 'string', 'max:100'],
            'phone'        => ['required', 'string', 'min:7', 'max:15'],
        ]);

        // Normalize phone
        $raw   = preg_replace('/\D/', '', $request->phone);
        $phone = '62' . ltrim($raw, '0');

        $customer = Customer::firstOrCreate(
            ['phone' => $phone],
            ['name' => $request->name, 'wa_id' => $phone]
        );

        // Update session
        session(['customer_id' => $customer->id, 'customer_name' => $customer->name]);

        $scheduledAt = \Illuminate\Support\Carbon::parse($request->scheduled_at);
        
        $service = Service::findOrFail($request->service_id);
        // $dpAmount = (int) ceil($service->price / 2);
        $dpAmount = 1000;

        // Create booking in TEMP_LOCKED state
        try {
            $booking = Booking::create([
                'customer_id'  => $customer->id,
                'barber_id'    => (int) $request->barber_id,
                'service_id'   => (int) $request->service_id,
                'scheduled_at' => $scheduledAt,
                'status'       => BookingStatus::TEMP_LOCKED->value,
                'dp_amount'    => $dpAmount,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique constraint violation = slot already booked
            if ($e->errorInfo[1] === 1062) {
                return response()->json(['error' => 'Slot sudah terisi. Pilih waktu lain.'], 409);
            }
            throw $e;
        }

        // Try to acquire slot lock
        $locked = $this->capacity->lockSlot($booking->id, $booking->barber_id, $scheduledAt);

        if (! $locked) {
            $booking->delete();
            return response()->json(['error' => 'Slot baru saja diambil. Coba waktu lain.'], 409);
        }

        // Generate QRIS
        try {
            $this->midtrans->createDPCharge($booking);
            $qrUrl  = $booking->midtrans_qr_code_url;

            return response()->json([
                'success'      => true,
                'booking_id'   => $booking->id,
                'qr_code_url'  => $qrUrl,
                'dp_amount'    => $booking->dp_amount,
                'expires_in'   => config('sisir.slot_lock_ttl'),
                'order_id'     => $booking->midtrans_order_id,
            ]);
        } catch (\Throwable $e) {
            // Midtrans unavailable — still return booking so user can pay later
            return response()->json([
                'success'     => true,
                'booking_id'  => $booking->id,
                'qr_code_url' => null,
                'warning'     => 'Gagal membuat QR pembayaran. Silakan hubungi admin.',
            ]);
        }
    }
}
