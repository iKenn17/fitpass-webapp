@props(['labels', 'values', 'color' => 'var(--green)', 'height' => 200])
@php $max = max(array_merge([1], $values)); @endphp
<div class="bars" style="--bar: {{ $color }}; height: {{ $height }}px;">
    @foreach ($labels as $i => $label)
        <div class="bar-col">
            <div class="bar-track">
                <div class="bar" style="height: {{ round(($values[$i] / $max) * 100) }}%" title="{{ $label }}: {{ number_format($values[$i]) }}"></div>
            </div>
            <span>{{ $label }}</span>
        </div>
    @endforeach
</div>
