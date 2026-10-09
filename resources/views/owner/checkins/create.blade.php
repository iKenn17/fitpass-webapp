@extends('layouts.owner')

@section('title', 'Manual Check-in')
@section('heading', 'CHECK-INS / MANUAL ENTRY')

@section('content')
    <section class="card form-card">
        <h2 class="card-title">Manual check-in</h2>
        <p class="muted" style="margin-bottom:16px">For members whose QR code can&rsquo;t be scanned. Only members with an unexpired plan are listed.</p>

        <form method="POST" action="{{ route('checkins.store') }}">
            @csrf

            <div class="field">
                <label for="member_id">Member</label>
                <select id="member_id" name="member_id" required>
                    <option value="">Select a member&hellip;</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" @selected((string) old('member_id') === (string) $member->id)>{{ $member->name }} ({{ $member->member_code }})</option>
                    @endforeach
                </select>
                @error('member_id')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="time_in">Time in</label>
                    <input id="time_in" type="datetime-local" name="time_in" value="{{ old('time_in', $now) }}" required>
                    @error('time_in')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="time_out">Time out (optional)</label>
                    <input id="time_out" type="datetime-local" name="time_out" value="{{ old('time_out') }}">
                    @error('time_out')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">CONFIRM CHECK-IN</button>
                <a class="btn" href="{{ route('checkins.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
