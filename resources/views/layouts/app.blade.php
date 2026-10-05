<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Система управления персоналом')</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/logo_orange.svg') }}">



    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/ui.css') }}?v={{ filemtime(public_path('css/ui.css')) }}" rel="stylesheet">
    
    @stack('styles')

</head>
<body>
    <div id="app">
        <nav>
            <button type="button" class="mobile-menu-toggle" id="menuToggle" aria-label="Меню" aria-controls="sidebar" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="container">
                <div class="app-name">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('icons/logo_orange.svg') }}" alt="ЗТЗ" class="app-logo">
                        <span class="app-name__full">Система управления персоналом</span><span class="app-name__short">Управление персоналом</span>
                    </a>
                </div>
                <div class="auth-links">
                    @guest
                        <!-- <a href="{{ route('login') }}">Вход</a> -->
                    @else
                        <span class="auth-user">{{ Auth::user()->name }}</span>
                        
                        <button type="button" class="notifications-icon" id="notificationsIcon" aria-label="Уведомления" data-url="{{ route('api.notifications') }}" data-read-url="{{ route('api.notifications.mark_as_read') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notifications-badge" id="notificationsBadge">0</span>
                        </button>
                        
                        <a href="#" class="auth-logout" aria-label="Выход" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <x-icon name="logout"/><span>Выход</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @endguest
                </div>
            </div>
        </nav>

        @auth
        <div class="layout">
            @include('layouts.partials.sidebar')
            <main class="layout__main">
                @include('partials.validation-errors')
                @if(isset($use_wrapper) && $use_wrapper)
                    @yield('content')
                @elseif(isset($special_container) && $special_container)
                    <div class="attendance-container">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        @yield('content')
                    </div>
                @else
                <div class="container">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    @yield('content')
                </div>
                @endif
            </main>
        </div>
        @else
        <main>
            <div class="container">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
        @endauth
    </div>
    
    @stack('scripts')
    
<script src="{{ asset('js/app.js') }}" defer></script>
    

</body>
</html> 