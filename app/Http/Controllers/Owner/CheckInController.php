<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CheckIn;
use App\Models\Member;
use App\Services\GymStats;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CheckInController extends Controller
{
    public function index(Request $request): View
    {
        $filter = in_array($request->query('filter'), ['qr', 'manual', 'inside'], true)
            ? $request->query('filter')
            : 'all';

        return view('owner.checkins.index', [
            'logs' => $this->todayQuery($filter)->paginate(10)->withQueryString(),
            'filter' => $filter,
            'stats' => $this->stats(),
        ]);
    }

    public function create(): View
    {
        return view('owner.checkins.create', [
            'members' => Member::active()->orderBy('name')->get(['id', 'name', 'member_code']),
            'now' => now()->format('Y-m-d\TH:i'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => ['required', Rule::exists('members', 'id')],
            'time_in' => ['required', 'date'],
            'time_out' => ['nullable', 'date', 'after_or_equal:time_in'],
        ]);

        $checkIn = CheckIn::create($data + [
            'method' => 'manual',
            'recorded_by' => $request->user()->id,
        ]);

        $member = $checkIn->member;
        AuditLog::record("{$request->user()->name} manually checked in {$member->name}", $request->user()->id);

        return redirect()->route('checkins.index')->with('status', "{$member->name} was checked in manually.");
    }

    /** CSV of today's logs. */
    public function export(Request $request): StreamedResponse
    {
        $filter = in_array($request->query('filter'), ['qr', 'manual', 'inside'], true)
            ? $request->query('filter')
            : 'all';

        $logs = $this->todayQuery($filter)->get();

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Member', 'Member ID', 'Type', 'Time in', 'Time out', 'Method', 'Status']);

            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->member->name,
                    $log->member->member_code,
                    $log->member->type,
                    $log->time_in->format('g:i A'),
                    $log->time_out?->format('g:i A') ?? '',
                    $log->method_label,
                    $log->is_inside ? 'In Gym' : 'Checked Out',
                ]);
            }

            fclose($out);
        }, 'checkins-'.today()->toDateString().'.csv', ['Content-Type' => 'text/csv']);
    }

    private function todayQuery(string $filter)
    {
        return CheckIn::with('member')
            ->today()
            ->when($filter === 'qr', fn ($q) => $q->where('method', 'qr'))
            ->when($filter === 'manual', fn ($q) => $q->where('method', 'manual'))
            ->when($filter === 'inside', fn ($q) => $q->whereNull('time_out'))
            ->latest('time_in');
    }

    private function stats(): array
    {
        $now = now();
        $startToday = today()->startOfDay();
        $startYesterday = today()->subDay()->startOfDay();
        $sameTimeYesterday = $now->copy()->subDay();

        $checkIns = CheckIn::whereBetween('time_in', [$startToday, $now])->count();
        $checkInsYesterday = CheckIn::whereBetween('time_in', [$startYesterday, $sameTimeYesterday])->count();

        $checkOuts = CheckIn::whereBetween('time_out', [$startToday, $now])->count();
        $checkOutsYesterday = CheckIn::whereBetween('time_out', [$startYesterday, $sameTimeYesterday])->count();

        $manual = CheckIn::where('method', 'manual')
            ->whereBetween('time_in', [$now->copy()->startOfMonth(), $now])
            ->count();
        $manualLastMonth = CheckIn::where('method', 'manual')
            ->whereBetween('time_in', [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow(),
            ])
            ->count();

        return [
            'check_ins' => ['value' => $checkIns, 'delta' => GymStats::pct($checkIns, $checkInsYesterday)],
            'check_outs' => ['value' => $checkOuts, 'delta' => GymStats::pct($checkOuts, $checkOutsYesterday)],
            'inside' => CheckIn::whereNull('time_out')->whereBetween('time_in', [$startToday, $now])->count(),
            'manual' => ['value' => $manual, 'delta' => GymStats::pct($manual, $manualLastMonth)],
        ];
    }
}
