<header class="topbar">
    <button type="button" id="nav-toggle" class="burger" aria-label="Toggle menu">
        <x-icon name="menu" />
    </button>

    {{-- Searches members by name, member ID or email. --}}
    <form class="search" method="GET" action="{{ route('members.index') }}" role="search">
        <x-icon name="search" />
        <input type="search" name="q" value="{{ request()->routeIs('members.index') ? request('q') : '' }}" placeholder="Search....." aria-label="Search members">
    </form>

    <details class="user">
        <summary>
            <span class="avatar" aria-hidden="true">{{ auth()->user()->initials }}</span>
            <span class="user-name">
                {{ auth()->user()->name }}
                <span class="user-role">{{ auth()->user()->role_label }}</span>
            </span>
            <x-icon name="chevron" class="chev" />
        </summary>
        <div class="user-menu">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Log out</button>
            </form>
        </div>
    </details>
</header>
