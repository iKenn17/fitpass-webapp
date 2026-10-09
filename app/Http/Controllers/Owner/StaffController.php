<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    /** Filter chip => role it maps to. */
    private const FILTERS = [
        'front_desk' => 'From Desk',
        'trainer' => 'Trainers',
        'admin' => 'Admins',
    ];

    public function index(Request $request): View
    {
        $requested = $request->query('filter');
        $filter = is_string($requested) && array_key_exists($requested, self::FILTERS) ? $requested : 'all';

        $active = User::staff()->where('is_active', true);

        $stats = [
            'total' => (clone $active)->count(),
            'added_this_month' => (clone $active)->where('created_at', '>=', now()->startOfMonth())->count(),
            'on_shift' => (clone $active)->where('shift_status', 'on_shift')->count(),
            'on_leave' => (clone $active)->where('shift_status', 'on_leave')->count(),
            'admins' => (clone $active)->where('role', 'admin')->count(),
        ];

        $staff = User::staff()
            ->when($filter !== 'all', fn ($q) => $q->where('role', $filter))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('owner.staff.index', [
            'staff' => $staff,
            'stats' => $stats,
            'filter' => $filter,
            'filters' => self::FILTERS,
        ]);
    }

    public function create(): View
    {
        return view('owner.staff.create', ['roles' => $this->assignableRoles()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys($this->assignableRoles()))],
            'shift_start' => ['nullable', 'date_format:H:i'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $staff = User::create($data + ['shift_status' => 'off_duty', 'is_active' => true]);

        AuditLog::record("{$request->user()->name} added staff account {$staff->name}", $request->user()->id);

        return redirect()->route('staff.index')->with('status', "{$staff->name} was added to staff.");
    }

    /** Deactivate or reactivate a staff account. The owner account can't be toggled. */
    public function toggle(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isOwner(), 403, 'The owner account cannot be deactivated.');

        $user->update([
            'is_active' => ! $user->is_active,
            'shift_status' => $user->is_active ? 'off_duty' : $user->shift_status,
        ]);

        $verb = $user->is_active ? 'reactivated' : 'deactivated';
        AuditLog::record("{$request->user()->name} {$verb} staff account {$user->name}", $request->user()->id);

        return back()->with('status', "{$user->name} was {$verb}.");
    }

    private function assignableRoles(): array
    {
        return collect(User::ROLE_LABELS)->except('owner')->all();
    }
}
