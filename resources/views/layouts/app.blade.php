<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Система управления персоналом')</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('icons/logo_orange.svg') }}">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    
    @stack('styles')

</head>
<body>
    <div id="app">
        <nav>
            <div class="mobile-menu-toggle" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
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
                        
                        <div class="notifications-icon" id="notificationsIcon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notifications-badge" id="notificationsBadge">0</span>
                        </div>
                        
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Управление мобильным меню
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.querySelector('.sidebar');
            const layoutMain = document.querySelector('.layout__main');
            
            // Инициализация уведомлений
            initNotifications();
            
            if (menuToggle && sidebar) {
                menuToggle.addEventListener('click', function() {
                    menuToggle.classList.toggle('active');
                    sidebar.classList.toggle('active');
                    
                    // Добавляем обработку для мобильных устройств
                    if (window.innerWidth <= 900) {
                        document.body.classList.toggle('menu-open');
                    }
                });
                
                // Закрывать меню при клике вне его области
                document.addEventListener('click', function(event) {
                    if (!sidebar.contains(event.target) && !menuToggle.contains(event.target) && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                        menuToggle.classList.remove('active');
                        document.body.classList.remove('menu-open');
                    }
                });
                
                // Обработка изменения размера окна
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 900) {
                        // Для десктопа
                        layoutMain.style.marginLeft = '250px';
                        layoutMain.style.width = 'calc(100% - 250px)';
                    } else {
                        // Для мобильных
                        layoutMain.style.marginLeft = '0';
                        layoutMain.style.width = '100%';
                    }
                });
                
                // Инициализация при загрузке страницы
                if (window.innerWidth <= 900) {
                    layoutMain.style.marginLeft = '0';
                    layoutMain.style.width = '100%';
                }

                // Сохранить активный пункт меню в localStorage
                const activeMenuItem = document.querySelector('.sidebar__link.active');
                if (activeMenuItem) {
                    localStorage.setItem('activeMenuItemHref', activeMenuItem.getAttribute('href'));
                }

                // Если нет активного пункта, но есть сохраненное значение
                if (!activeMenuItem && localStorage.getItem('activeMenuItemHref')) {
                    const savedHref = localStorage.getItem('activeMenuItemHref');
                    const menuItems = document.querySelectorAll('.sidebar__link');
                    menuItems.forEach(item => {
                        if (item.getAttribute('href') === savedHref) {
                            item.classList.add('active');
                        }
                    });
                }
            }
        });
        
        // Функция для инициализации уведомлений
        function initNotifications() {
            const notificationsIcon = document.getElementById('notificationsIcon');
            const notificationsBadge = document.getElementById('notificationsBadge');
            
            if (notificationsIcon && notificationsBadge) {
                // Загружаем уведомления с сервера
                loadNotifications();
                
                // Устанавливаем обработчик клика
                notificationsIcon.addEventListener('click', function() {
                    showNotificationsModal();
                });
                
                // Обновляем уведомления каждые 5 минут
                setInterval(loadNotifications, 300000);
            }
        }
        
        // Загрузка уведомлений с сервера
        function loadNotifications() {
            fetch('{{ route("api.notifications") }}')
                .then(response => response.json())
                .then(data => {
                    // Обновляем счетчик уведомлений
                    const badge = document.getElementById('notificationsBadge');
                    if (badge) {
                        // Считаем только непрочитанные уведомления
                        const count = data.filter(notification => !notification.is_read).length;
                        badge.textContent = count;
                        
                        if (count > 0) {
                            badge.classList.add('has-notifications');
                        } else {
                            badge.classList.remove('has-notifications');
                        }
                    }
                })
                .catch(error => console.error('Ошибка загрузки уведомлений:', error));
        }
        
        // Показ модального окна с уведомлениями
        function showNotificationsModal() {
            fetch('{{ route("api.notifications") }}')
                .then(response => response.json())
                .then(data => {
                    // Создаем модальное окно для отображения уведомлений
                    const modal = document.createElement('div');
                    modal.className = 'notification-modal';
                    
                    const modalContent = document.createElement('div');
                    modalContent.className = 'notification-modal__content';
                    
                    const modalHeader = document.createElement('div');
                    modalHeader.className = 'notification-modal__header';
                    modalHeader.innerHTML = `
                        <h2>Уведомления</h2>
                        <div class="notification-modal__actions">
                            <button class="notification-modal__mark-all-btn">Отметить все как прочитанные</button>
                            <button class="notification-modal__close">&times;</button>
                        </div>
                    `;
                    
                    const modalBody = document.createElement('div');
                    modalBody.className = 'notification-modal__body';
                    
                    // Если есть уведомления, выводим их
                    if (data.length > 0) {
                        let notificationsHtml = '';
                        
                        data.forEach(notification => {
                            let notificationTypeClass = 'notification-item';
                            let iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
                            
                            if (notification.type === 'important') {
                                notificationTypeClass += ' notification-item--important';
                                iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
                            } else if (notification.type === 'warning') {
                                notificationTypeClass += ' notification-item--warning';
                                iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
                            }
                            
                            // Добавляем класс для прочитанных уведомлений
                            if (notification.is_read) {
                                notificationTypeClass += ' notification-item--read';
                            }
                            
                            const createdAt = new Date(notification.created_at);
                            const formattedDate = createdAt.toLocaleDateString('ru-RU') + ' ' + createdAt.toLocaleTimeString('ru-RU');
                            
                            notificationsHtml += `
                                <div class="${notificationTypeClass}" data-id="${notification.id}">
                                    <div class="notification-item__icon">
                                        ${iconSvg}
                                    </div>
                                    <div class="notification-item__content">
                                        <div class="notification-item__title">
                                            ${notification.title}
                                            ${!notification.is_read ? '<span class="notification-item__unread-badge">Новое</span>' : ''}
                                        </div>
                                        <div class="notification-item__text">${notification.message}</div>
                                        <div class="notification-item__date">${formattedDate}</div>
                                    </div>
                                    ${!notification.is_read ? `<button class="notification-item__mark-btn" data-id="${notification.id}">Отметить как прочитанное</button>` : ''}
                                </div>
                            `;
                        });
                        
                        modalBody.innerHTML = notificationsHtml;
                    } else {
                        modalBody.innerHTML = '<p class="notification-modal__empty">У вас нет уведомлений</p>';
                    }
                    
                    modalContent.appendChild(modalHeader);
                    modalContent.appendChild(modalBody);
                    modal.appendChild(modalContent);
                    document.body.appendChild(modal);
                    
                    // Обработчик закрытия модального окна
                    const closeBtn = modal.querySelector('.notification-modal__close');
                    closeBtn.addEventListener('click', function() {
                        document.body.removeChild(modal);
                    });
                    
                    // Закрытие по клику вне модального окна
                    modal.addEventListener('click', function(event) {
                        if (event.target === modal) {
                            document.body.removeChild(modal);
                        }
                    });
                    
                    // Обработчик для кнопки "Отметить все как прочитанные"
                    const markAllBtn = modal.querySelector('.notification-modal__mark-all-btn');
                    if (markAllBtn) {
                        markAllBtn.addEventListener('click', function() {
                            markAllNotificationsAsRead(modal);
                        });
                    }
                    
                    // Обработчики для кнопок "Отметить как прочитанное" у отдельных уведомлений
                    const markBtns = modal.querySelectorAll('.notification-item__mark-btn');
                    markBtns.forEach(btn => {
                        btn.addEventListener('click', function() {
                            const notificationId = this.getAttribute('data-id');
                            markNotificationAsRead(notificationId, this.closest('.notification-item'));
                        });
                    });
                })
                .catch(error => console.error('Ошибка загрузки уведомлений:', error));
        }
        
        // Функция для отметки всех уведомлений как прочитанных
        function markAllNotificationsAsRead(modal) {
            fetch('{{ route("api.notifications.mark_as_read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем внешний вид уведомлений в модальном окне
                    const notificationItems = modal.querySelectorAll('.notification-item:not(.notification-item--read)');
                    notificationItems.forEach(item => {
                        item.classList.add('notification-item--read');
                        const badge = item.querySelector('.notification-item__unread-badge');
                        if (badge) badge.remove();
                        const markBtn = item.querySelector('.notification-item__mark-btn');
                        if (markBtn) markBtn.remove();
                    });
                    
                    // Обновляем счетчик уведомлений
                    const badge = document.getElementById('notificationsBadge');
                    if (badge) {
                        badge.textContent = '0';
                        badge.classList.remove('has-notifications');
                    }
                }
            })
            .catch(error => console.error('Ошибка отметки уведомлений как прочитанных:', error));
        }
        
        // Функция для отметки конкретного уведомления как прочитанного
        function markNotificationAsRead(notificationId, notificationElement) {
            fetch('{{ route("api.notifications.mark_as_read") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    notification_ids: [notificationId]
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем внешний вид уведомления
                    notificationElement.classList.add('notification-item--read');
                    const badge = notificationElement.querySelector('.notification-item__unread-badge');
                    if (badge) badge.remove();
                    const markBtn = notificationElement.querySelector('.notification-item__mark-btn');
                    if (markBtn) markBtn.remove();
                    
                    // Обновляем счетчик уведомлений
                    loadNotifications();
                }
            })
            .catch(error => console.error('Ошибка отметки уведомления как прочитанного:', error));
        }
    </script>
    
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