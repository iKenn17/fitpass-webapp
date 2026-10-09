<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'reports' ? 'reports' : 'audit';

        return view('owner.audit', [
            'tab' => $tab,
            'logs' => $tab === 'audit'
                ? AuditLog::latest()->latest('id')->paginate(15)->withQueryString()
                : null,
            'report' => $tab === 'reports' ? $this->report() : null,
        ]);
    }

    /** Simple period summary for the Reports tab. */
    private function report(): array
    {
        $now = now();
        $periods = [
            'Today' => $now->copy()->startOfDay(),
            'This week' => $now->copy()->startOfWeek(),
            'This month' => $now->copy()->startOfMonth(),
        ];

        $rows = [];

        foreach ($periods as $label => $from) {
            $rows[] = [
                'period' => $label,
                'check_ins' => CheckIn::whereBetween('time_in', [$from, $now])->count(),
                'new_members' => Member::whereDate('joined_at', '>=', $from->toDateString())->count(),
                'revenue' => (float) Payment::whereBetween('paid_at', [$from, $now])->sum('amount'),
            ];
        }

        return $rows;
    }
}
