{{-- Expects $overview from App\Services\GymStats::overview() --}}
<section class="card stats" aria-label="Key numbers">
    <div class="stat">
        <div class="stat-label">Total Member</div>
        <div class="stat-value">{{ number_format($overview['members']['value']) }}</div>
        <div class="stat-sub"><x-delta :value="$overview['members']['delta']" label="vs yesterday" /></div>
    </div>
    <div class="stat">
        <div class="stat-label">Total Check-ins</div>
        <div class="stat-value">{{ number_format($overview['check_ins']['value']) }}</div>
        <div class="stat-sub"><x-delta :value="$overview['check_ins']['delta']" label="vs yesterday" /></div>
    </div>
    <div class="stat">
        <div class="stat-label">Active Member</div>
        <div class="stat-value">{{ number_format($overview['active']['value']) }}</div>
        <div class="stat-sub"><x-delta :value="$overview['active']['delta']" label="vs last month" /></div>
    </div>
    <div class="stat">
        <div class="stat-label">Active Revenue</div>
        <div class="stat-value">₱{{ number_format($overview['revenue']['value']) }}</div>
        <div class="stat-sub"><x-delta :value="$overview['revenue']['delta']" label="vs last month" /></div>
    </div>
</section>
