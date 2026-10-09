<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\GymStats;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __invoke(GymStats $stats): View
    {
        return view('owner.analytics', [
            'overview' => $stats->overview(),
            'week' => $stats->checkInSeries(7, 'D'),
            'revenue' => $stats->revenueByType(),
            'peak' => $stats->peakHours(),
            'growth' => $stats->memberGrowth(6),
        ]);
    }
}
