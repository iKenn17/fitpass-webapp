<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · FitPass Gym</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind:wght@400;500;600&family=Holtwood+One+SC&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/fitpass.css') }}">
</head>
<body>
    <div class="app">
        @include('owner.partials.sidebar')

        <div class="main">
            @include('owner.partials.topbar')

            <main>
                <h1 class="page-title">@yield('heading')</h1>

                @if (session('status'))
                    <div class="flash" role="status">{{ session('status') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Hamburger: collapse the sidebar on desktop, open it on small screens.
        document.getElementById('nav-toggle')?.addEventListener('click', function () {
            var wide = window.matchMedia('(min-width: 861px)').matches;
            document.body.classList.toggle(wide ? 'nav-collapsed' : 'nav-open');
        });
    </script>
</body>
</html>
