{{-- Drop the emblem exported from Figma at public/images/logo.png to replace the "FP" badge. --}}
<a href="{{ route('dashboard') }}" class="logo" aria-label="FitPass Gym">
    @if (file_exists(public_path('images/logo.png')))
        <img class="logo-mark" src="{{ asset('images/logo.png') }}" alt="">
    @else
        <span class="logo-badge" aria-hidden="true">FP</span>
    @endif
    <span class="logo-text">
        <span class="logo-word">FIT<span class="pass">PASS</span></span>
        <span class="logo-sub">GYM</span>
    </span>
</a>
