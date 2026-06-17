<?php

namespace App\Http\Controllers;

use App\Jobs\SlotRecoveryBroadcastJob;
use App\Models\Customer;
use App\Models\Waitlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoController extends Controller
{
    /**
     * GET /promo
     */
    public function index(): View
    {
        $user = auth()->user();

        return view('sisir.promo', compact('user'));
    }

    /**
     * POST /promo/send
     * Trigger a promo broadcast via WhatsApp to all active waitlist customers.
     * In production this would be admin-only. For demo, any logged-in user can trigger.
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'discount_amount' => ['required', 'numeric', 'min:1000', 'max:500000'],
        ]);

        $discountPct = 15; // Fixed 15% for demo; could compute from amount

        SlotRecoveryBroadcastJob::dispatch(
            now()->addHour()->toIso8601String(),
            $discountPct
        )->onQueue('broadcasts');

        return response()->json([
            'success' => true,
            'message' => 'Promo broadcast dijadwalkan! Pesan akan dikirim ke semua pelanggan waitlist.',
        ]);
    }

    /**
     * POST /promo/waitlist
     * Join the waitlist from the promo page.
     */
    public function joinWaitlist(Request $request): JsonResponse
    {
        $customerId = session('customer_id');

        if (! $customerId) {
            return response()->json(['error' => 'Silakan login terlebih dahulu.'], 401);
        }

        Waitlist::firstOrCreate(
            ['customer_id' => $customerId, 'is_active' => true],
            ['preferred_date' => today()]
        );

        return response()->json([
            'success' => true,
            'message' => '✅ Kamu masuk daftar tunggu. Kami akan notifikasi jika ada slot tersedia!',
        ]);
    }
}
