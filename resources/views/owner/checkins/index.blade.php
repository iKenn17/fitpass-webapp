@extends('layouts.owner')

@section('title', 'Check-in')
@section('heading', 'CHECK-INS')

@php
    $chips = ['all' => 'All', 'qr' => 'QR-code', 'manual' => 'Manual', 'inside' => 'Currently In'];
    $cols = 'grid-template-columns: 2.2fr 1fr 1fr 1fr 1.2fr 1.2fr;';
@endphp

@section('content')
    <section class="card stats">
        <div class="stat">
            <div class="stat-label">Check-ins <span class="muted">Today</span></div>
            <div class="stat-value">{{ number_format($stats['check_ins']['value']) }}</div>
            <div class="stat-sub"><x-delta :value="$stats['check_ins']['delta']" label="vs yesterday" /></div>
        </div>
        <div class="stat">
            <div class="stat-label">Check-outs <span class="muted">Today</span></div>
            <div class="stat-value">{{ number_format($stats['check_outs']['value']) }}</div>
            <div class="stat-sub"><x-delta :value="$stats['check_outs']['delta']" label="vs yesterday" /></div>
        </div>
        <div class="stat">
            <div class="stat-label">Still in Gym</div>
            <div class="stat-value">{{ number_format($stats['inside']) }}</div>
            <div class="stat-sub muted">right now</div>
        </div>
        <div class="stat">
            <div class="stat-label">Manual Entries</div>
            <div class="stat-value">{{ number_format($stats['manual']['value']) }}</div>
            <div class="stat-sub"><x-delta :value="$stats['manual']['delta']" label="vs last month" /></div>
        </div>
    </section>

    <section class="card" style="margin-top:16px">
        <h2 class="card-title">Today&rsquo;s Check-in Logs</h2>

        <div class="toolbar">
            @foreach ($chips as $key => $label)
                <a class="chip {{ $filter === $key ? 'active' : '' }}" href="{{ route('checkins.index', $key === 'all' ? [] : ['filter' => $key]) }}">{{ $label }}</a>
            @endforeach

            <span class="spacer"></span>
            <a class="btn" href="{{ route('checkins.export', $filter === 'all' ? [] : ['filter' => $filter]) }}">Export</a>
            <a class="btn" href="{{ route('checkins.create') }}"><x-icon name="plus" /> Manual Entry</a>
        </div>

        @if ($logs->isEmpty())
            <p class="empty">No check-ins match this view today.</p>
        @else
            <div class="rows">
                <div class="row row-head" style="{{ $cols }}">
                    <span>MEMBER</span><span>TITLE</span><span>TIME IN</span><span>TIME OUT</span><span>METHOD</span><span>STATUS</span>
                </div>

                @foreach ($logs as $log)
                    <div class="row" style="{{ $cols }}">
                        <div class="cell-person">
                            <span class="initials">{{ $log->member->initials }}</span>
                            <div style="min-width:0">
                                <div class="name">{{ $log->member->name }}</div>
                                <div class="sub">{{ $log->member->member_code }}</div>
                            </div>
                        </div>
                        <span>{{ $log->member->type }}</span>
                        <span>{{ $log->time_in->format('g:i A') }}</span>
                        <span>{{ $log->time_out?->format('g:i A') ?? '----' }}</span>
                        <span><span class="badge plain"><i></i>{{ $log->method === 'qr' ? 'QR Code' : 'MANUAL' }}</span></span>
                        <span>
                            @if ($log->is_inside)
                                <span class="badge green"><i></i>In Gym</span>
                            @else
                                <span class="badge"><i></i>Checked Out</span>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        @include('owner.partials.pager', ['paginator' => $logs])
    </section>
@endsection
