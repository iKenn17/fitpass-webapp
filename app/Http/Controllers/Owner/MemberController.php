<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $filter = is_string($request->query('filter')) ? $request->query('filter') : 'all';
        $q = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $view = $request->query('view') === 'grid' ? 'grid' : 'table';

        $members = Member::query()
            ->search($q)
            ->when(in_array($filter, Member::TYPES, true), fn ($q) => $q->where('type', $filter))
            ->when($filter === 'expiring', fn ($q) => $q->expiringSoon())
            ->orderBy('name')
            ->paginate($view === 'grid' ? 12 : 10)
            ->withQueryString();

        return view('owner.members.index', [
            'members' => $members,
            'filter' => $filter,
            'view' => $view,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('owner.members.create', [
            'today' => today()->toDateString(),
            'defaultExpiry' => today()->addMonth()->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'type' => ['required', Rule::in(Member::TYPES)],
            'plan' => ['required', Rule::in(Member::PLANS)],
            'joined_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after_or_equal:joined_at'],
        ]);

        $member = Member::create($data + ['member_code' => Member::nextCode()]);

        AuditLog::record("{$request->user()->name} added new member {$member->name}", $request->user()->id);

        return redirect()->route('members.index')->with('status', "{$member->name} was added as {$member->member_code}.");
    }
}
