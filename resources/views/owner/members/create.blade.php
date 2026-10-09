@extends('layouts.owner')

@section('title', 'Add Member')
@section('heading', 'MEMBERS / MANUAL ENTRY')

@section('content')
    <section class="card form-card">
        <h2 class="card-title">Add a member</h2>

        <form method="POST" action="{{ route('members.store') }}">
            @csrf

            <div class="field">
                <label for="name">Full name</label>
                <input id="name" name="name" value="{{ old('name') }}" required autofocus>
                @error('name')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="email">Email (optional)</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}">
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="phone">Phone (optional)</label>
                    <input id="phone" name="phone" value="{{ old('phone') }}">
                    @error('phone')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="type">Plan</label>
                    <select id="type" name="type" required>
                        @foreach (\App\Models\Member::TYPES as $type)
                            <option value="{{ $type }}" @selected(old('type', 'Regular') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('type')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="plan">Type</label>
                    <select id="plan" name="plan" required>
                        @foreach (\App\Models\Member::PLANS as $plan)
                            <option value="{{ $plan }}" @selected(old('plan', 'Monthly Unlimited') === $plan)>{{ $plan }}</option>
                        @endforeach
                    </select>
                    @error('plan')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="joined_at">Joined</label>
                    <input id="joined_at" type="date" name="joined_at" value="{{ old('joined_at', $today) }}" required>
                    @error('joined_at')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="expires_at">Expires</label>
                    <input id="expires_at" type="date" name="expires_at" value="{{ old('expires_at', $defaultExpiry) }}" required>
                    @error('expires_at')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">ADD MEMBER</button>
                <a class="btn" href="{{ route('members.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
