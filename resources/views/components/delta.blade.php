@props(['value' => null, 'label'])
<span class="delta">
    @if ($value === null)
        <span class="muted">—</span>
    @else
        <span class="{{ $value >= 0 ? 'up' : 'down' }}">{{ $value >= 0 ? '▲' : '▼' }} {{ rtrim(rtrim(number_format(abs($value), 1), '0'), '.') }}%</span>
    @endif
    {{ $label }}
</span>
