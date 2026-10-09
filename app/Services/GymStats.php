<?php

namespace App\Services;

use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Numbers shown on the owner Dashboard and Analytics pages.
 * Written to run on both SQLite and MySQL (no DB-specific date functions).
 */
class GymStats
{
    public const TYPE_COLORS = [
        'Regular' => '#00c853',
        'Student' => '#2563eb',
        'Senior' => '#7fd3e6',
        'Walk-in' => '#9ca3af',
    ];

    /** [label, start hour (inclusive), end hour (exclusive)] */
    public const PEAK_SLOTS = [
        ['6:00 AM - 8:00 AM', 6, 8],
        ['8:00 AM - 11:00 AM', 8, 11],
        ['5:00 PM - 8:00 PM', 17, 20],
        ['8:00 PM - 10:00 PM', 20, 22],
        ['10:00 PM - 12:00 AM', 22, 24],
    ];

    public static function pct(float|int $current, float|int $previous): ?float
    {
        if ($previous <= 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /** The four headline cards. `delta` is a % change or null when there is nothing to compare. */
    public function overview(): array
    {
        $now = now();
        $today = today();
        $yesterday = $today->copy()->subDay();
        $monthAgo = $today->copy()->subDays(30);

        // Total members: now vs end of yesterday.
        $members = Member::count();
        $membersYesterday = Member::whereDate('joined_at', '<=', $yesterday->toDateString())->count();

        // Total check-ins: all time. Delta compares today so far with yesterday up to the same time.
        $checkIns = CheckIn::count();
        $todaySoFar = CheckIn::whereBetween('time_in', [$today->copy()->startOfDay(), $now])->count();
        $yesterdaySameTime = CheckIn::whereBetween('time_in', [$yesterday->copy()->startOfDay(), $now->copy()->subDay()])->count();

        // Active members: now vs 30 days ago.
        $active = Member::active()->count();
        $activeThen = Member::whereDate('joined_at', '<=', $monthAgo->toDateString())
            ->whereDate('expires_at', '>=', $monthAgo->toDateString())
            ->count();

        // Revenue: month to date vs the same span of last month.
        $lastMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $lastMonthSameTime = $now->copy()->subMonthNoOverflow();
        $revenue = (float) Payment::whereBetween('paid_at', [$now->copy()->startOfMonth(), $now])->sum('amount');
        $revenueThen = (float) Payment::whereBetween('paid_at', [$lastMonthStart, $lastMonthSameTime])->sum('amount');

        return [
            'members' => ['value' => $members, 'delta' => self::pct($members, $membersYesterday)],
            'check_ins' => ['value' => $checkIns, 'delta' => self::pct($todaySoFar, $yesterdaySameTime)],
            'active' => ['value' => $active, 'delta' => self::pct($active, $activeThen)],
            'revenue' => ['value' => $revenue, 'delta' => self::pct($revenue, $revenueThen)],
        ];
    }

    /** Check-ins per day for the last $days days, oldest first. */
    public function checkInSeries(int $days, string $labelFormat = 'M j'): array
    {
        $start = today()->subDays($days - 1);

        $counts = CheckIn::where('time_in', '>=', $start->copy()->startOfDay())
            ->get(['time_in'])
            ->countBy(fn ($row) => $row->time_in->toDateString());

        $labels = [];
        $values = [];

        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $labels[] = $day->format($labelFormat);
            $values[] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return compact('labels', 'values');
    }

    /** Busiest days by check-in count. */
    public function topDays(int $limit = 6): array
    {
        return CheckIn::selectRaw('DATE(time_in) as day, COUNT(*) as total')
            ->groupByRaw('DATE(time_in)')
            ->orderByDesc('total')
            ->orderByDesc('day')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'date' => \Carbon\Carbon::parse($row->day)->format('M j, Y'),
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /** Members split by Regular / Student / Senior. */
    public function typeBreakdown(): array
    {
        $rows = Member::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        return $this->segments($rows, Member::TYPES);
    }

    /** Month-to-date revenue split by member type, plus walk-ins. */
    public function revenueByType(): array
    {
        $rows = Payment::leftJoin('members', 'members.id', '=', 'payments.member_id')
            ->whereBetween('payments.paid_at', [now()->startOfMonth(), now()])
            ->selectRaw("COALESCE(members.type, 'Walk-in') as label, SUM(payments.amount) as total")
            ->groupByRaw("COALESCE(members.type, 'Walk-in')")
            ->pluck('total', 'label');

        return $this->segments($rows, [...Member::TYPES, 'Walk-in']);
    }

    /**
     * Check-ins over the last 30 days bucketed into time slots, ranked
     * Peak / High / Medium / Low / Soft (busiest first).
     */
    public function peakHours(): array
    {
        $counts = array_fill(0, count(self::PEAK_SLOTS), 0);

        CheckIn::where('time_in', '>=', today()->subDays(29)->startOfDay())
            ->get(['time_in'])
            ->each(function ($row) use (&$counts) {
                $hour = (int) $row->time_in->format('G');

                foreach (self::PEAK_SLOTS as $i => [, $from, $to]) {
                    if ($hour >= $from && $hour < $to) {
                        $counts[$i]++;
                        break;
                    }
                }
            });

        $ranks = ['Peak', 'High', 'Medium', 'Low', 'Soft'];
        $order = collect($counts)->sortDesc()->keys()->values();

        $rows = [];
        foreach (self::PEAK_SLOTS as $i => [$label]) {
            $rows[] = [
                'label' => $label,
                'total' => $counts[$i],
                'level' => $ranks[$order->search($i)] ?? 'Soft',
            ];
        }

        return $rows;
    }

    /** Total members at the end of each of the last $months months. */
    public function memberGrowth(int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = today()->startOfMonth()->subMonthsNoOverflow($i);
            $labels[] = $month->format('M');
            $values[] = Member::whereDate('joined_at', '<=', $month->copy()->endOfMonth()->toDateString())->count();
        }

        return compact('labels', 'values');
    }

    /**
     * Only the database is actually probed. QR scanner, payment gateway and
     * backup are placeholders until those integrations exist.
     */
    public function systemStatus(): array
    {
        try {
            DB::connection()->getPdo();
            $database = true;
        } catch (\Throwable) {
            $database = false;
        }

        return [
            ['label' => 'QR Scanner', 'online' => true],
            ['label' => 'Database', 'online' => $database],
            ['label' => 'Payment Gateway', 'online' => true],
            ['label' => 'Backup', 'online' => true],
        ];
    }

    private function segments($rows, array $order): array
    {
        $sum = max((float) collect($rows)->sum(), 1);

        return collect($order)
            ->map(fn ($label) => [
                'label' => $label,
                'value' => (float) ($rows[$label] ?? 0),
                'pct' => (int) round(((float) ($rows[$label] ?? 0)) / $sum * 100),
                'color' => self::TYPE_COLORS[$label] ?? '#9ca3af',
            ])
            ->all();
    }
}
