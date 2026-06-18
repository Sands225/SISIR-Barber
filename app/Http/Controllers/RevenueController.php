<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RevenueController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', 'month'); // today | week | month | year
        $now    = Carbon::now('Asia/Jakarta');

        [$startDate, $endDate] = match ($period) {
            'today' => [$now->copy()->startOfDay(),  $now->copy()->endOfDay()],
            'week'  => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'year'  => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()], // month
        };

        // Base query: only paid bookings (DP Lunas = BOOKED and above)
        $paidStatuses = [
            BookingStatus::BOOKED->value,
            BookingStatus::CONFIRMED->value,
            BookingStatus::IN_SERVICE->value,
            BookingStatus::COMPLETED->value,
        ];

        $baseQuery = Booking::with(['customer', 'service', 'barber'])
            ->whereIn('status', $paidStatuses)
            ->whereBetween('scheduled_at', [$startDate, $endDate]);

        // ── Summary Stats ────────────────────────────────────────────────────
        $totalRevenue      = (clone $baseQuery)->sum('dp_amount');
        $totalTransactions = (clone $baseQuery)->count();
        $completedRevenue  = (clone $baseQuery)
            ->where('status', BookingStatus::COMPLETED->value)
            ->sum('dp_amount');
        $avgPerTransaction = $totalTransactions > 0
            ? round($totalRevenue / $totalTransactions)
            : 0;

        $countCompleted = (clone $baseQuery)
            ->where('status', BookingStatus::COMPLETED->value)
            ->count();

        $countCancelled = Booking::whereIn('status', [
                BookingStatus::CANCELLED_BY_SYSTEM->value,
                BookingStatus::NO_SHOW->value,
            ])
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->count();

        // ── Revenue by Service ───────────────────────────────────────────────
        $revenueByService = Booking::selectRaw('service_id, SUM(dp_amount) as total, COUNT(*) as count')
            ->whereIn('status', $paidStatuses)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->with('service')
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->get();

        // ── Revenue by Barber ────────────────────────────────────────────────
        $revenueByBarber = Booking::selectRaw('barber_id, SUM(dp_amount) as total, COUNT(*) as count')
            ->whereIn('status', $paidStatuses)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->with('barber.user')
            ->groupBy('barber_id')
            ->orderByDesc('total')
            ->get();

        // ── Daily Revenue Chart (last 7 days for 'week'/'today', or per-day for month) ──
        $chartDays   = $period === 'year' ? 12 : min($now->diffInDays($startDate) + 1, 30);
        $chartLabels = [];
        $chartData   = [];

        if ($period === 'year') {
            for ($m = 1; $m <= 12; $m++) {
                $ms = Carbon::create($now->year, $m, 1, 0, 0, 0, 'Asia/Jakarta');
                $me = $ms->copy()->endOfMonth();
                $chartLabels[] = $ms->translatedFormat('M');
                $chartData[]   = Booking::whereIn('status', $paidStatuses)
                    ->whereBetween('scheduled_at', [$ms, $me])
                    ->sum('dp_amount');
            }
        } else {
            $days = (int) ceil($startDate->diffInDays($endDate)) + 1;
            $days = min($days, 31);
            for ($i = 0; $i < $days; $i++) {
                $day = $startDate->copy()->addDays($i);
                $chartLabels[] = $day->format('d/m');
                $chartData[]   = Booking::whereIn('status', $paidStatuses)
                    ->whereDate('scheduled_at', $day->toDateString())
                    ->sum('dp_amount');
            }
        }

        // ── Recent Paid Transactions ─────────────────────────────────────────
        $recentTransactions = (clone $baseQuery)
            ->orderByDesc('scheduled_at')
            ->limit(20)
            ->get();

        // ── Full Service Revenue (harga layanan × booking selesai) ───────────
        // This represents total billable amount when service is completed
        $completedBookings = Booking::with('service')
            ->where('status', BookingStatus::COMPLETED->value)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->get();

        $fullServiceRevenue = $completedBookings->sum(fn ($b) => $b->service?->price ?? 0);

        // ── Grand Total (DP from all paid + remaining from completed services) ─
        // totalRevenue = sum of DP paid
        // fullServiceRevenue = sum of service prices for completed bookings
        // grandTotal = DP already received + remaining service fees (completed only)
        $remainingServiceRevenue = $fullServiceRevenue - $completedRevenue; // non-DP portion of completed
        $grandTotal = $totalRevenue + max(0, $remainingServiceRevenue);

        // ── Period Labels ────────────────────────────────────────────────────
        $periodLabels = [
            'today' => 'Hari Ini',
            'week'  => 'Minggu Ini',
            'month' => 'Bulan Ini',
            'year'  => 'Tahun Ini',
        ];

        return view('sisir.revenue', compact(
            'period',
            'periodLabels',
            'totalRevenue',
            'totalTransactions',
            'completedRevenue',
            'avgPerTransaction',
            'countCompleted',
            'countCancelled',
            'fullServiceRevenue',
            'grandTotal',
            'revenueByService',
            'revenueByBarber',
            'recentTransactions',
            'chartLabels',
            'chartData',
            'startDate',
            'endDate',
        ));
    }
}
