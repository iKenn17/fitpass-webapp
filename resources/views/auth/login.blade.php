<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in · FitPass Gym</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind:wght@400;500;600&family=Holtwood+One+SC&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/fitpass.css') }}">
</head>
<body>
    <div class="login">
        <div class="login-brand">
            <x-logo />
        </div>

        <div class="login-panel">
            <form class="login-form" method="POST" action="{{ route('login.attempt') }}">
                @csrf

                <div class="field">
                    <label class="sr-only" for="email" style="position:absolute;left:-9999px">Administrator</label>
                    <input id="email" type="text" name="email" value="{{ old('email') }}" placeholder="Administrator" autocomplete="username" required autofocus>
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label class="sr-only" for="password" style="position:absolute;left:-9999px">Password</label>
                    <input id="password" type="password" name="password" placeholder="Password" autocomplete="current-password" required>
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>

                <label class="remember">
                    <input type="checkbox" name="remember" value="1"> Keep me signed in
                </label>

                <button type="submit" class="btn btn-primary">LOG IN</button>
            </form>
        </div>
    </div>
</body>
</html>
