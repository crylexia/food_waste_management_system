<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'EcoManager') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/navigation.js') }}" defer></script>
</head>
<body>
    @auth
    <header class="header">
        <div class="header-content">
            <h1 class="logo">EcoManager</h1>
            <div class="header-right">
                <span class="user-name">{{ Auth::user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;" onsubmit="if(window.EcoManager && window.EcoManager.clearNavigationState) window.EcoManager.clearNavigationState();">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <div class="main-container">
        <main class="content">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('items.index') }}" class="nav-item {{ request()->routeIs('items.*') ? 'active' : '' }}">
                    Items
                </a>
                <a href="{{ route('entries.index') }}" class="nav-item {{ request()->routeIs('entries.*') ? 'active' : '' }}">
                    Daily Entry
                </a>
                <a href="{{ route('records.index') }}" class="nav-item {{ request()->routeIs('records.*') ? 'active' : '' }}">
                    Records
                </a>
                <a href="{{ route('analytics.index') }}" class="nav-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                    Analytics
                </a>
            </nav>
        </aside>
    </div>
    @else
    <div class="guest-container">
        @yield('content')
    </div>
    @endauth
</body>
</html>
