@extends('layouts.owner')

@section('title', 'Members')
@section('heading', 'MEMBERS')

@php
    $chips = ['all' => 'All', 'Regular' => 'Regular', 'Student' => 'Student', 'Senior' => 'Senior', 'expiring' => 'Expiring soon'];
    $badgeTone = ['Active' => 'green', 'Expiring soon' => 'amber', 'Expired' => 'red'];
@endphp

@section('content')
    <section class="card">
        <h2 class="card-title">All Members</h2>

        <div class="toolbar">
            @foreach ($chips as $key => $label)
                <a class="chip {{ $filter === $key ? 'active' : '' }}"
                   href="{{ route('members.index', array_filter(['filter' => $key === 'all' ? null : $key, 'q' => $q, 'view' => $view === 'grid' ? 'grid' : null])) }}">{{ $label }}</a>
            @endforeach

            <span class="spacer"></span>

            <span class="seg">
                <a class="{{ $view === 'table' ? 'active' : '' }}" href="{{ route('members.index', array_filter(['filter' => $filter === 'all' ? null : $filter, 'q' => $q])) }}">Table</a>
                <a class="{{ $view === 'grid' ? 'active' : '' }}" href="{{ route('members.index', array_filter(['filter' => $filter === 'all' ? null : $filter, 'q' => $q, 'view' => 'grid'])) }}">Grid</a>
            </span>

            <a class="btn" href="{{ route('members.create') }}"><x-icon name="plus" /> Manual Entry</a>
        </div>

        @if ($q !== '')
            <p class="muted" style="margin:4px 0 8px">Results for &ldquo;{{ $q }}&rdquo; &middot; <a href="{{ route('members.index') }}" style="text-decoration:underline">clear</a></p>
        @endif

        @if ($members->isEmpty())
            <p class="empty">No members match this view.</p>
        @elseif ($view === 'grid')
            <div class="member-grid">
                @foreach ($members as $member)
                    <article class="member-card">
                        <div class="cell-person">
                            <span class="initials">{{ $member->initials }}</span>
                            <div style="min-width:0">
                                <div class="name">{{ $member->name }}</div>
                                <div class="sub">{{ $member->member_code }}</div>
                            </div>
                        </div>
                        <dl>
                            <dt>Plan</dt><dd>{{ $member->type }}</dd>
                            <dt>Type</dt><dd>{{ $member->plan }}</dd>
                            <dt>Joined</dt><dd>{{ $member->joined_at->format('M j, Y') }}</dd>
                            <dt>Expires</dt><dd>{{ $member->expires_at->format('M j, Y') }}</dd>
                        </dl>
                        <span class="badge {{ $badgeTone[$member->status] }}"><i></i>{{ $member->status }}</span>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rows">
                @php $cols = 'grid-template-columns: 2.2fr 1fr 1.4fr 1fr 1fr 1.2fr;'; @endphp
                <div class="row row-head" style="{{ $cols }}">
                    <span>MEMBER</span><span>PLAN</span><span>TYPE</span><span>JOINED</span><span>EXPIRES</span><span>STATUS</span>
                </div>

                @foreach ($members as $member)
                    <div class="row" style="{{ $cols }}">
                        <div class="cell-person">
                            <span class="initials">{{ $member->initials }}</span>
                            <div style="min-width:0">
                                <div class="name">{{ $member->name }}</div>
                                <div class="sub">{{ $member->member_code }}</div>
                            </div>
                        </div>
                        <span>{{ $member->type }}</span>
                        <span>{{ $member->plan }}</span>
                        <span>{{ $member->joined_at->format('M j, Y') }}</span>
                        <span>{{ $member->expires_at->format('M j, Y') }}</span>
                        <span><span class="badge {{ $badgeTone[$member->status] }}"><i></i>{{ $member->status }}</span></span>
                    </div>
                @endforeach
            </div>
        @endif

        @include('owner.partials.pager', ['paginator' => $members])
    </section>
@endsection
