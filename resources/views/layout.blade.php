{{-- The frame of every page: <head>, the menu at the top and the message box.
     Every page starts with @extends('layout') and puts its content into @section('content'). --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - TradeLog</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <nav class="navbar">
        <a href="{{ route('dashboard') }}" class="logo">Trade<span>Log</span></a>

        @auth
            {{-- Logged-in users see this --}}
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('trades.index') }}" class="{{ request()->routeIs('trades.*') ? 'active' : '' }}">Trades</a>

            <div class="nav-right">
                <a href="{{ route('trades.create') }}" class="btn btn-accent">+ New trade</a>
                <span class="user">{{ Auth::user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Log out</button>
                </form>
            </div>
        @else
            {{-- Guests see this --}}
            <div class="nav-right">
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}" class="btn btn-accent">Register</a>
            </div>
        @endauth
    </nav>

    <div class="container">
        {{-- Message after saving or deleting (the controller sets it with ->with('success', '...')) --}}
        @if (session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        {{-- Here comes the content of the page --}}
        @yield('content')
    </div>

    {{-- JavaScript of the page (only some pages have it) --}}
    @yield('scripts')

</body>
</html>
