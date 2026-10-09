@php
    $items = [
        ['route' => 'dashboard', 'match' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'grid'],
        ['route' => 'analytics', 'match' => 'analytics', 'label' => 'Analytics', 'icon' => 'chart'],
        ['route' => 'members.index', 'match' => 'members.*', 'label' => 'Members', 'icon' => 'users'],
        ['route' => 'staff.index', 'match' => 'staff.*', 'label' => 'Staff Management', 'icon' => 'id'],
        ['route' => 'checkins.index', 'match' => 'checkins.*', 'label' => 'Check-in', 'icon' => 'login'],
        ['route' => 'audit.index', 'match' => 'audit.*', 'label' => 'Audit Logs', 'icon' => 'log'],
    ];
@endphp
<aside class="sidebar">
    <x-logo />

    <nav class="nav" aria-label="Owner navigation">
        @foreach ($items as $item)
            <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['match']) ? 'active' : '' }}" @if (request()->routeIs($item['match'])) aria-current="page" @endif>
                <x-icon :name="$item['icon']" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</aside>
