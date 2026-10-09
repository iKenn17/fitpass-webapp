@extends('layouts.owner')

@section('title', 'Analytics')
@section('heading', 'ANALYTICS')

@section('content')
    @include('owner.partials.stat-strip', ['overview' => $overview])

    <div class="grid-2 even">
        <section class="card">
            <div class="card-head">
                <h2 class="card-title" style="font-size:21px">Check-in &mdash; Last 7 Days</h2>
                <span class="pill-select">This week</span>
            </div>
            <x-bars :labels="$week['labels']" :values="$week['values']" :height="210" />
        </section>

        <section class="card">
            <h2 class="card-title" style="font-size:21px">Revenue by plan <span class="muted" style="font-size:14px">(this month)</span></h2>
            <x-donut :segments="$revenue" :size="200" />
        </section>
    </div>

    <div class="grid-2 even">
        <section class="card">
            <h2 class="card-title" style="font-size:21px">Peak Hours <span class="muted" style="font-size:14px">(last 30 days)</span></h2>
            <table class="peak">
                @foreach ($peak as $slot)
                    <tr>
                        <td>{{ $slot['label'] }}</td>
                        <td>{{ $slot['level'] }}</td>
                    </tr>
                @endforeach
            </table>
        </section>

        <section class="card">
            <h2 class="card-title" style="font-size:21px">Member Growth (6 Months)</h2>
            <x-bars :labels="$growth['labels']" :values="$growth['values']" color="var(--blue)" :height="210" />
        </section>
    </div>
@endsection
