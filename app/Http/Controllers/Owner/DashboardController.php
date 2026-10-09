<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Services\GymStats;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, GymStats $stats): View
    {
        $range = $request->query('range') === 'month' ? 'month' : 'week';

        return view('owner.dashboard', [
            'overview' => $stats->overview(),
            'recent' => CheckIn::with('member')->latest('time_in')->limit(5)->get(),
            'range' => $range,
            'series' => $stats->checkInSeries($range === 'month' ? 30 : 7),
            'topDays' => $stats->topDays(6),
            'types' => $stats->typeBreakdown(),
            'status' => $stats->systemStatus(),
        ]);
    }
}
