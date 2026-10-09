@extends('layouts.owner')

@section('title', 'Staff Management')
@section('heading', 'STAFF MANAGEMENT')

@php
    $statusTone = ['On Shift' => 'green', 'Off Duty' => 'plain', 'On Leave' => 'amber', 'Deactivated' => 'red'];
    $cols = 'grid-template-columns: 2.2fr 1.2fr 1fr 1.2fr 1.6fr 1fr;';
@endphp

@section('content')
    <section class="card stats">
        <div class="stat">
            <div class="stat-label">Total staff</div>
            <div class="stat-value" style="font-size:30px">{{ $stats['total'] }}</div>
            <div class="stat-sub"><span class="up">{{ $stats['added_this_month'] }} added this month</span></div>
        </div>
        <div class="stat">
            <div class="stat-label">Active Today</div>
            <div class="stat-value" style="font-size:30px">{{ $stats['on_shift'] }}</div>
            <div class="stat-sub"><span class="up">On Shift now</span></div>
        </div>
        <div class="stat">
            <div class="stat-label">On leave</div>
            <div class="stat-value" style="font-size:30px">{{ $stats['on_leave'] }}</div>
            <div class="stat-sub"><span class="warn">Returning next week</span></div>
        </div>
        <div class="stat">
            <div class="stat-label">Admins</div>
            <div class="stat-value" style="font-size:30px">{{ $stats['admins'] }}</div>
            <div class="stat-sub"><span class="up">Full Access</span></div>
        </div>
    </section>

    <section class="card" style="margin-top:16px">
        <div class="toolbar">
            <a class="chip {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('staff.index') }}">All</a>
            @foreach ($filters as $role => $label)
                <a class="chip {{ $filter === $role ? 'active' : '' }}" href="{{ route('staff.index', ['filter' => $role]) }}">{{ $label }}</a>
            @endforeach

            <span class="spacer"></span>
            <a class="btn" href="{{ route('staff.create') }}"><x-icon name="plus" /> Add Staff</a>
        </div>

        @if ($staff->isEmpty())
            <p class="empty">No staff in this view.</p>
        @else
            <div class="rows">
                <div class="row row-head" style="{{ $cols }}">
                    <span>STAFF</span><span>ROLE</span><span>SHIFT</span><span>STATUS</span><span>LAST LOGIN</span><span></span>
                </div>

                @foreach ($staff as $member)
                    <div class="row" style="{{ $cols }} {{ $member->is_active ? '' : 'opacity:.6' }}">
                        <div class="cell-person">
                            <span class="initials">{{ $member->initials }}</span>
                            <div style="min-width:0">
                                <div class="name">{{ $member->name }}</div>
                                <div class="sub">{{ $member->email }}</div>
                            </div>
                        </div>
                        <span>{{ $member->role_label }}</span>
                        <span>{{ $member->shift_label }}</span>
                        <span><span class="badge {{ $statusTone[$member->shift_status_label] ?? 'plain' }}"><i></i>{{ $member->shift_status_label }}</span></span>
                        <span>{{ $member->last_login_label }}</span>
                        <form method="POST" action="{{ route('staff.toggle', $member) }}" style="justify-self:end"
                              onsubmit="return confirm('{{ $member->is_active ? 'Deactivate' : 'Reactivate' }} {{ e(addslashes($member->name)) }}?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn small {{ $member->is_active ? 'btn-danger' : '' }}">
                                {{ $member->is_active ? 'Deactivate' : 'Reactivate' }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        @include('owner.partials.pager', ['paginator' => $staff])
    </section>
@endsection
