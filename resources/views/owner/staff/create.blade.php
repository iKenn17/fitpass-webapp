@extends('layouts.owner')

@section('title', 'Add Staff')
@section('heading', 'STAFF MANAGEMENT / ADD STAFF')

@section('content')
    <section class="card form-card">
        <h2 class="card-title">Add a staff account</h2>

        <form method="POST" action="{{ route('staff.store') }}">
            @csrf

            <div class="field">
                <label for="name">Full name</label>
                <input id="name" name="name" value="{{ old('name') }}" required autofocus>
                @error('name')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', 'front_desk') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="shift_start">Shift starts (optional)</label>
                    <input id="shift_start" type="time" name="shift_start" value="{{ old('shift_start') }}">
                    @error('shift_start')<span class="error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="field">
                <label for="password">Temporary password</label>
                <input id="password" type="password" name="password" minlength="8" required autocomplete="new-password">
                @error('password')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">ADD STAFF</button>
                <a class="btn" href="{{ route('staff.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
