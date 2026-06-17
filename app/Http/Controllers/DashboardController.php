<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * GET /dashboard
     * Admin/Barber dashboard with today's real bookings.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Today's active bookings for all customers
        $todayBookings = Booking::with(['barber.user', 'service', 'customer'])
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at')
            ->get();

        // Summary counts for antrean card (shop-wide, today)
        $todayTotal = Booking::whereDate('scheduled_at', today())
            ->whereNotIn('status', [
                BookingStatus::CANCELLED_BY_SYSTEM->value,
                BookingStatus::NO_SHOW->value,
            ])
            ->count();

        // Recent bookings (last 10 from any date)
        $recentBookings = Booking::with(['barber.user', 'service', 'customer'])
            ->orderByDesc('scheduled_at')
            ->limit(10)
            ->get();

        return view('sisir.dashboard', compact(
            'user',
            'todayBookings',
            'todayTotal',
            'recentBookings',
        ));
    }
}
