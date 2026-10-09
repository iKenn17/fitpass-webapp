@props(['labels', 'values', 'height' => 250])
@php
    $width = 700;
    $padLeft = 42;
    $padRight = 14;
    $padTop = 12;
    $padBottom = 30;
    $count = count($values);

    // Round the top of the axis up to a tidy number so gridlines read 0, 25, 50...
    $max = max(array_merge([4], $values));
    $raw = $max / 4;
    $magnitude = 10 ** floor(log10($raw));
    $unit = $magnitude;
    foreach ([1, 2, 2.5, 5, 10] as $step) {
        $unit = $step * $magnitude;
        if ($unit >= $raw) {
            break;
        }
    }
    $top = $unit * 4;

    $plotW = $width - $padLeft - $padRight;
    $plotH = $height - $padTop - $padBottom;
    $x = fn ($i) => $padLeft + ($count > 1 ? $i * $plotW / ($count - 1) : $plotW / 2);
    $y = fn ($v) => $padTop + (1 - $v / $top) * $plotH;

    $points = [];
    foreach ($values as $i => $v) {
        $points[] = round($x($i), 1).','.round($y($v), 1);
    }

    $area = $count > 0
        ? 'M'.round($x(0), 1).','.round($y(0), 1).' L'.implode(' L', $points).' L'.round($x($count - 1), 1).','.round($y(0), 1).' Z'
        : '';

    $every = max(1, (int) ceil($count / 8));
@endphp
<svg class="chart" viewBox="0 0 {{ $width }} {{ $height }}" role="img" aria-label="Check-ins over time">
    @for ($g = 0; $g <= 4; $g++)
        @php $gy = $y($unit * $g); @endphp
        <line class="grid" x1="{{ $padLeft }}" x2="{{ $width - $padRight }}" y1="{{ round($gy, 1) }}" y2="{{ round($gy, 1) }}"/>
        <text x="{{ $padLeft - 8 }}" y="{{ round($gy + 4, 1) }}" text-anchor="end">{{ rtrim(rtrim(number_format($unit * $g, 1), '0'), '.') }}</text>
    @endfor

    @if ($count > 0)
        <path class="area" d="{{ $area }}"/>
        <polyline class="line" points="{{ implode(' ', $points) }}"/>
        @foreach ($values as $i => $v)
            <circle class="dot" cx="{{ round($x($i), 1) }}" cy="{{ round($y($v), 1) }}" r="3.5"><title>{{ $labels[$i] }}: {{ $v }}</title></circle>
        @endforeach
        @foreach ($labels as $i => $label)
            @if ($i % $every === 0)
                <text x="{{ round($x($i), 1) }}" y="{{ $height - 8 }}" text-anchor="middle">{{ $label }}</text>
            @endif
        @endforeach
    @endif
</svg>
