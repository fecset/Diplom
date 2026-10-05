<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Система управления персоналом')</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/logo_orange.svg') }}">



    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    
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
                        <span>Система управления персоналом</span>
                    </a>
                </div>
                <div class="auth-links">
                    @guest
                        <!-- <a href="{{ route('login') }}">Вход</a> -->
                    @else
                        <span>{{ Auth::user()->name }}</span>
                        
                        <button type="button" class="notifications-icon" id="notificationsIcon" aria-label="Уведомления" data-url="{{ route('api.notifications') }}" data-read-url="{{ route('api.notifications.mark_as_read') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notifications-badge" id="notificationsBadge">0</span>
                        </button>
                        
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Выход
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
    
    <style>
        /* Стили для иконки уведомлений */
        .notifications-icon {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            cursor: pointer;
            color: var(--color-white);
            transition: color 0.2s ease;
        }
        
        .app-logo {
            height: 32px;
            width: auto;
            margin-right: 10px;
            vertical-align: middle;
        }
        
        .app-name a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--color-white);
        }
        
        .notifications-icon:hover {
            color: var(--color-primary);
        }
        
        .notifications-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--color-primary);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .notifications-badge.has-notifications {
            opacity: 1;
        }
        
        /* Стили для модального окна с уведомлениями */
        .notification-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .notification-modal__content {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .notification-modal__header {
            padding: 15px 20px;
            background-color: #f7f7f7;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-modal__header h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .notification-modal__close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        
        .notification-modal__body {
            padding: 20px;
            overflow-y: auto;
            max-height: calc(80vh - 60px);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .notification-modal__empty {
            text-align: center;
            color: #666;
            padding: 30px 0;
        }
        
        /* Стили для уведомлений в модальном окне */
        .notification-item {
            display: flex;
            padding: 12px 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            border-left: 3px solid #ccc;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }
        
        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
        }
        
        .notification-item--important {
            border-left-color: #ff5252;
            background-color: #fff8f8;
        }
        
        .notification-item--warning {
            border-left-color: #ff9800;
            background-color: #fff8e1;
        }
        
        .notification-item--read {
            opacity: 0.7;
        }
        
        .notification-item__icon {
            flex: 0 0 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: #666;
        }
        
        .notification-item--important .notification-item__icon {
            color: #ff5252;
        }
        
        .notification-item--warning .notification-item__icon {
            color: #ff9800;
        }
        
        .notification-item__content {
            flex: 1;
        }
        
        .notification-item__title {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--color-black);
            display: flex;
            align-items: center;
        }
        
        .notification-item__unread-badge {
            display: inline-block;
            background-color: var(--color-primary);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 8px;
            font-weight: 700;
        }
        
        .notification-item__text {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .notification-item__date {
            font-size: 12px;
            color: #888;
        }
        
        .notification-item__mark-btn {
            position: absolute;
            right: 15px;
            bottom: 10px;
            background-color: transparent;
            border: none;
            color: var(--color-primary);
            font-size: 12px;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
        }
        
        .notification-item__mark-btn:hover {
            color: var(--color-accent);
        }
        
        .notification-modal__actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .notification-modal__mark-all-btn {
            background: none;
            border: none;
            color: var(--color-primary);
            font-size: 14px;
            cursor: pointer;
            text-decoration: underline;
        }
        
        .notification-modal__mark-all-btn:hover {
            color: var(--color-accent);
        }
    </style>
</body>
</html> 