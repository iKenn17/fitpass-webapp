@extends('layouts.owner')

@section('title', 'Dashboard')
@section('heading', 'DASHBOARD')

@section('content')
    @include('owner.partials.stat-strip', ['overview' => $overview])

    <div class="grid-2">
        <section class="card">
            <h2 class="card-title">Recent Check-ins</h2>

            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Time</th>
                        <th class="right">Methods</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recent as $log)
                        <tr>
                            <td>{{ $log->member->name }}</td>
                            <td>{{ $log->member->type }}</td>
                            <td>{{ $log->time_in->format('g:i A') }}</td>
                            <td class="right">{{ $log->method_label }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">No check-ins yet.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <p style="text-align:center;margin-top:8px"><a href="{{ route('checkins.index') }}" class="muted">View All</a></p>
        </section>

        <section class="card">
            <div class="card-head">
                <h2 class="card-title">Check-ins Overview</h2>
                <form method="GET" action="{{ route('dashboard') }}">
                    <select name="range" class="pill-select" onchange="this.form.submit()" aria-label="Range">
                        <option value="week" @selected($range === 'week')>This Week</option>
                        <option value="month" @selected($range === 'month')>This Month</option>
                    </select>
                </form>
            </div>

            <x-line-chart :labels="$series['labels']" :values="$series['values']" />
        </section>
    </div>

    <div class="grid-3">
        <section class="card">
            <h2 class="card-title" style="font-size:20px">Top performing Days</h2>
            <ol class="rank">
                @forelse ($topDays as $day)
                    <li><span>{{ $day['date'] }}</span><span>{{ number_format($day['total']) }}</span></li>
                @empty
                    <li class="muted">No data yet.</li>
                @endforelse
            </ol>
        </section>

        <section class="card">
            <h2 class="card-title" style="font-size:20px">Member by Type</h2>
            <x-donut :segments="$types" :size="170" />
        </section>

        <section class="card">
            <h2 class="card-title">System Status</h2>
            <div class="status-list">
                @foreach ($status as $item)
                    <div>
                        <span>{{ $item['label'] }}</span>
                        <span class="{{ $item['online'] ? 'on' : 'off' }}">{{ $item['online'] ? 'Online' : 'Offline' }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
