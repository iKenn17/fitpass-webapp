@props(['segments', 'size' => 190])
@php
    $total = collect($segments)->sum('value');
    $cursor = 0;
    $stops = [];

    foreach ($segments as $segment) {
        $from = $cursor;
        $cursor += $total > 0 ? ($segment['value'] / $total) * 100 : 0;
        $stops[] = $segment['color'].' '.round($from, 2).'% '.round($cursor, 2).'%';
    }

    if ($total <= 0) {
        $stops = ['#3a3a3a 0% 100%'];
    }
@endphp
<div class="donut-wrap">
    <div class="donut" style="--size: {{ $size }}px; background: conic-gradient({{ implode(', ', $stops) }});" role="img" aria-label="Breakdown chart"><span></span></div>
    <div class="legend">
        @foreach ($segments as $segment)
            <div>
                <i style="background: {{ $segment['color'] }}"></i>
                <span>{{ $segment['label'] }}</span>
                <b>{{ $segment['pct'] }}%</b>
            </div>
        @endforeach
    </div>
</div>
