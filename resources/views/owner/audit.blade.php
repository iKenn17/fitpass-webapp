@extends('layouts.owner')

@section('title', 'Audit Logs')
@section('heading', 'REPORTS & AUDIT LOGS')

@section('content')
    <section class="card">
        <nav class="tabs" aria-label="Reports and audit logs">
            <a href="{{ route('audit.index', ['tab' => 'reports']) }}" class="{{ $tab === 'reports' ? 'active' : '' }}">Reports</a>
            <a href="{{ route('audit.index') }}" class="{{ $tab === 'audit' ? 'active' : '' }}">Audit Logs</a>
        </nav>

        @if ($tab === 'audit')
            @forelse ($logs as $log)
                <div class="log">
                    <time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->when_label }}</time>
                    <span class="msg">{{ $log->message }}</span>
                </div>
            @empty
                <p class="empty">Nothing has been logged yet.</p>
            @endforelse

            @include('owner.partials.pager', ['paginator' => $logs])
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th class="right">Check-ins</th>
                        <th class="right">New members</th>
                        <th class="right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report as $row)
                        <tr>
                            <td>{{ $row['period'] }}</td>
                            <td class="right">{{ number_format($row['check_ins']) }}</td>
                            <td class="right">{{ number_format($row['new_members']) }}</td>
                            <td class="right">₱{{ number_format($row['revenue']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
@endsection
