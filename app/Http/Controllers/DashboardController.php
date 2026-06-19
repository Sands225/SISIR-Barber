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

        // Today's bookings with "Sudah DP" status (BOOKED or CONFIRMED)
        $todayBookings = Booking::with(['barber.user', 'service', 'customer'])
            ->whereDate('scheduled_at', today())
            ->whereIn('status', [BookingStatus::BOOKED, BookingStatus::CONFIRMED])
            ->orderBy('scheduled_at')
            ->get();

        // Summary counts for dashboard cards
        $todayTotalBookings = Booking::whereDate('scheduled_at', today())->count();
        
        $yesterdayTotalBookings = Booking::whereDate('scheduled_at', today()->subDay())->count();
        $bookingTrend = 0;
        if ($yesterdayTotalBookings > 0) {
            $bookingTrend = round((($todayTotalBookings - $yesterdayTotalBookings) / $yesterdayTotalBookings) * 100);
        } else if ($todayTotalBookings > 0) {
            $bookingTrend = 100;
        }

        $todayInServiceCount = Booking::whereDate('scheduled_at', today())
            ->where('status', BookingStatus::IN_SERVICE)
            ->count();

        $todayCompletedCount = Booking::whereDate('scheduled_at', today())
            ->where('status', BookingStatus::COMPLETED)
            ->count();

        $totalCustomersCount = Customer::count();

        // Status counts for today's bookings (donut chart)
        $statusCounts = Booking::whereDate('scheduled_at', today())
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $dilayaniCount = $statusCounts[BookingStatus::IN_SERVICE->value] ?? 0;
        $menungguCount = $statusCounts[BookingStatus::TEMP_LOCKED->value] ?? 0;
        $sudahDpCount = ($statusCounts[BookingStatus::BOOKED->value] ?? 0) + ($statusCounts[BookingStatus::CONFIRMED->value] ?? 0);
        $batalCount = ($statusCounts[BookingStatus::CANCELLED_BY_SYSTEM->value] ?? 0) + ($statusCounts[BookingStatus::NO_SHOW->value] ?? 0);
        $selesaiCount = $statusCounts[BookingStatus::COMPLETED->value] ?? 0;

        $totalStats = $dilayaniCount + $menungguCount + $sudahDpCount + $batalCount + $selesaiCount;

        $gradientSegments = [];
        if ($totalStats > 0) {
            $currentPercent = 0;
            $segments = [
                ['color' => '#1e7c3a', 'count' => $dilayaniCount],     // Dilayani: Green
                ['color' => '#f9a825', 'count' => $menungguCount],     // Menunggu DP: Orange/Yellow
                ['color' => '#4285f4', 'count' => $sudahDpCount],       // Sudah DP: Blue
                ['color' => '#d93025', 'count' => $batalCount],         // Batal: Red
                ['color' => '#a3e6b9', 'count' => $selesaiCount],       // Selesai: Light Green
            ];
            foreach ($segments as $segment) {
                if ($segment['count'] > 0) {
                    $percent = ($segment['count'] / $totalStats) * 100;
                    $nextPercent = $currentPercent + $percent;
                    $gradientSegments[] = "{$segment['color']} {$currentPercent}% {$nextPercent}%";
                    $currentPercent = $nextPercent;
                }
            }
        }
        $conicGradient = !empty($gradientSegments) ? implode(', ', $gradientSegments) : '#e5e7eb 0% 100%';

        // Recent activities based on booking updates
        $activityBookings = Booking::with(['customer', 'service'])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $recentActivities = $activityBookings->map(function ($booking) {
            $time = $booking->updated_at->locale('id')->diffForHumans();
            $name = $booking->customer->name ?? 'Pelanggan';
            $service = $booking->service->name ?? 'Layanan';

            switch ($booking->status) {
                case BookingStatus::TEMP_LOCKED:
                    return [
                        'title' => "Booking baru dari {$name}",
                        'subtitle' => $service,
                        'time' => $time,
                        'icon' => 'calendar',
                        'color_class' => 'text-[var(--green-600)] bg-[var(--green-50)]',
                    ];
                case BookingStatus::BOOKED:
                case BookingStatus::CONFIRMED:
                    return [
                        'title' => "DP diterima dari {$name}",
                        'subtitle' => $service,
                        'time' => $time,
                        'icon' => 'dp',
                        'color_class' => 'text-[var(--orange-500)] bg-[var(--orange-100)]',
                    ];
                case BookingStatus::IN_SERVICE:
                    return [
                        'title' => "Mulai melayani {$name}",
                        'subtitle' => $service,
                        'time' => $time,
                        'icon' => 'service',
                        'color_class' => 'text-blue-600 bg-blue-50',
                    ];
                case BookingStatus::COMPLETED:
                    return [
                        'title' => "Booking selesai - {$name}",
                        'subtitle' => $service,
                        'time' => $time,
                        'icon' => 'completed',
                        'color_class' => 'text-[var(--green-600)] bg-[var(--green-50)]',
                    ];
                case BookingStatus::CANCELLED_BY_SYSTEM:
                case BookingStatus::NO_SHOW:
                    return [
                        'title' => "Booking dibatalkan - {$name}",
                        'subtitle' => $service,
                        'time' => $time,
                        'icon' => 'cancelled',
                        'color_class' => 'text-[var(--red-500)] bg-[var(--red-100)]',
                    ];
                default:
                    return null;
            }
        })->filter()->values()->toArray();

        return view('sisir.dashboard', compact(
            'user',
            'todayBookings',
            'todayTotalBookings',
            'bookingTrend',
            'todayInServiceCount',
            'todayCompletedCount',
            'totalCustomersCount',
            'dilayaniCount',
            'menungguCount',
            'sudahDpCount',
            'batalCount',
            'selesaiCount',
            'conicGradient',
            'recentActivities',
        ));
    }
}
