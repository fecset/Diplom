@extends('layouts.app')
@section('title', 'Личный кабинет')

@section('content')
<div class="dashboard">
    <div class="dashboard__header">
        <h1 class="dashboard__title">Личный кабинет</h1>
    </div>
    
    <!-- Карточки с основной информацией -->
    <div class="dashboard__cards">
        <div class="dashboard__card dashboard__card--profile">
            <div class="dashboard__card-header">
                <h2 class="dashboard__card-title">Личные данные</h2>
                <span class="dashboard__card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </span>
            </div>
            <div class="dashboard__card-content">
                <div class="profile__info">
                    <div class="profile__row">
                        <span class="profile__label">ФИО:</span>
                        <span class="profile__value">{{ $user->name }}</span>
                    </div>
                    <div class="profile__row">
                        <span class="profile__label">Должность:</span>
                        <span class="profile__value">{{ $user->position?->name ?? '—' }}</span>
                    </div>
                    <div class="profile__row">
                        <span class="profile__label">Отдел:</span>
                        <span class="profile__value">{{ $user->department?->name ?? '—' }}</span>
                    </div>
                    <div class="profile__row">
                        <span class="profile__label">Телефон:</span>
                        <span class="profile__value">{{ $user->phone_number ?? '—' }}</span>
                    </div>
                    <div class="profile__row">
                        <span class="profile__label">Логин:</span>
                        <span class="profile__value">{{ $user->username }}</span>
                    </div>
                    <div class="profile__row">
                        <span class="profile__label">Email:</span>
                        <span class="profile__value">{{ $user->email ?? '—' }}</span>
                    </div>
                    <div class="profile__row">
                        <span class="profile__label">Дата приёма:</span>
                        <span class="profile__value">{{ $user->hired_at ? \Carbon\Carbon::parse($user->hired_at)->format('d.m.Y') : '—' }}</span>
                    </div>
                    <div class="profile__row">
                        <span class="profile__label">Трудовой стаж:</span>
                        <span class="profile__value">
                            @if($experience)
                                {{ $experience['years'] }} {{ trans_choice('год|года|лет', $experience['years']) }}
                                {{ $experience['months'] }} {{ trans_choice('месяц|месяца|месяцев', $experience['months']) }}
                                {{ $experience['days'] }} {{ trans_choice('день|дня|дней', $experience['days']) }}
                            @else
                                —
                            @endif
                        </span>
                    </div>
                </div>

                <div class="profile__actions">
                    <button class="btn btn--secondary" onclick="window.location.href='{{ route('profile.downloadCertificate') }}'">Скачать справку с места работы</button>
                </div>
            </div>
        </div>
        
        <div class="dashboard__card dashboard__card--stats">
            <div class="dashboard__card-header">
                <h2 class="dashboard__card-title">Статистика посещаемости</h2>
                <span class="dashboard__card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </span>
            </div>
            <div class="dashboard__card-content">
                <div class="attendance-stats">
                    <div class="attendance-stats__item attendance-stats__item--present">
                        <div class="attendance-stats__count">{{ $attendanceStats['present'] }}</div>
                        <div class="attendance-stats__label">Явки</div>
                    </div>
                    <div class="attendance-stats__item attendance-stats__item--absent">
                        <div class="attendance-stats__count">{{ $attendanceStats['absent'] }}</div>
                        <div class="attendance-stats__label">Неявки</div>
                    </div>
                    <div class="attendance-stats__item attendance-stats__item--vacation">
                        <div class="attendance-stats__count">{{ $attendanceStats['vacation'] }}</div>
                        <div class="attendance-stats__label">Отпуск</div>
                    </div>
                    <div class="attendance-stats__item attendance-stats__item--sick">
                        <div class="attendance-stats__count">{{ $attendanceStats['sick_leave'] }}</div>
                        <div class="attendance-stats__label">Больничный</div>
                    </div>
                </div>
                <div class="attendance-stats__footer">
                    <a href="{{ route('hr.attendance.index') }}" class="dashboard__link">Перейти к табелю</a>
                </div>
            </div>
        </div>
        
        <div class="dashboard__card dashboard__card--vacation">
            <div class="dashboard__card-header">
                <h2 class="dashboard__card-title">Отпуск</h2>
                <span class="dashboard__card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-1.5"></path><path d="M12 12h9"></path><path d="M16 16l5-5"></path><path d="M16 8l5 5"></path></svg>
                </span>
            </div>
            <div class="dashboard__card-content">
                <div class="vacation-info">
                    <div class="vacation-info__item">
                        <span class="vacation-info__label">Осталось дней отпуска в этом году:</span>
                        <span class="vacation-info__value">{{ $vacationDaysLeft }}</span>
                    </div>
                    <div class="vacation-info__item">
                        <span class="vacation-info__label">Заявок на рассмотрении:</span>
                        <span class="vacation-info__value">{{ $pendingRequests }}</span>
                    </div>
                </div>
                <div class="vacation-info__buttons">
                    <a href="{{ route('leave_requests.vacation.create') }}" class="btn btn--primary">Новая заявка на отпуск</a>
                    <a href="{{ route('leave_requests.sick_leave.create') }}" class="btn btn--primary">Новая заявка на больничный</a>
                    <a href="{{ route('leave_requests.business_trip.create') }}" class="btn btn--primary">Новая заявка на командировку</a>
                </div>
            </div>
        </div>
        
        <div class="dashboard__card dashboard__card--notifications">
            <div class="dashboard__card-header">
                <h2 class="dashboard__card-title">Уведомления и события</h2>
                <span class="dashboard__card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </span>
            </div>
            <div class="dashboard__card-content">
                <div class="notifications-list">
                    @forelse($userNotifications as $notification)
                        <div class="notification-item {{ $notification->type == 'important' ? 'notification-item--important' : ($notification->type == 'warning' ? 'notification-item--warning' : '') }}">
                            <div class="notification-item__icon">
                                @if($notification->type == 'important')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                @elseif($notification->type == 'warning')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                                @endif
                            </div>
                            <div class="notification-item__content">
                                <div class="notification-item__title">{{ $notification->title }}</div>
                                <div class="notification-item__text">{{ $notification->message }}</div>
                                <div class="notification-item__date">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="notification-item">
                            <div class="notification-item__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                            </div>
                            <div class="notification-item__content">
                                <div class="notification-item__title">Нет новых уведомлений</div>
                                <div class="notification-item__text">В настоящее время у вас нет новых уведомлений</div>
                            </div>
                        </div>
                    @endforelse
                </div>
                @if(count($userNotifications) > 0)
                    <div class="notifications-footer">
                        <button class="notifications-footer__btn" id="showAllNotifications">Показать все уведомления</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Секция с последними заявками -->
    <div class="dashboard__section">
        <div class="dashboard__section-header">
            <h2 class="dashboard__section-title">Последние заявки</h2>
            <a href="{{ route('leave_requests.vacation') }}" class="dashboard__link dashboard__view-all">Посмотреть все</a>
        </div>
        
        <div class="dashboard__section-content">
            @if(count($recentLeaveRequests) > 0)
                <div class="leave-requests-list">
                    @foreach($recentLeaveRequests as $request)
                        <div class="leave-request-card">
                            <div class="leave-request-card__header">
                                <span class="leave-request-card__type {{ $request->type === 'vacation' ? 'leave-request-card__type--vacation' : 'leave-request-card__type--sick' }}">
                                    {{ $request->type === 'vacation' ? 'Отпуск' : 'Больничный' }}
                                </span>
                                <span class="leave-request-card__status leave-request-card__status--{{ $request->status }}">
                                    @if($request->status === 'new')
                                        На рассмотрении
                                    @elseif($request->status === 'approved')
                                        Одобрена
                                    @elseif($request->status === 'rejected')
                                        Отклонена
                                    @endif
                                </span>
                            </div>
                            <div class="leave-request-card__content">
                                <div class="leave-request-card__row">
                                    <span class="leave-request-card__label">Период:</span>
                                    <span class="leave-request-card__value">
                                        {{ \Carbon\Carbon::parse($request->date_start)->format('d.m.Y') }} - 
                                        {{ \Carbon\Carbon::parse($request->date_end)->format('d.m.Y') }}
                                    </span>
                                </div>
                                @if($request->reason)
                                <div class="leave-request-card__row">
                                    <span class="leave-request-card__label">Причина:</span>
                                    <span class="leave-request-card__value">{{ $request->reason }}</span>
                                </div>
                                @endif
                                @if($request->hr_comment)
                                <div class="leave-request-card__row">
                                    <span class="leave-request-card__label">Комментарий HR:</span>
                                    <span class="leave-request-card__value">{{ $request->hr_comment }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="leave-request-card__footer">
                                <span class="leave-request-card__date">Создана {{ \Carbon\Carbon::parse($request->created_at)->format('d.m.Y H:i') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="dashboard__empty-state">
                    <p>У вас пока нет заявок. <a href="{{ route('leave_requests.vacation.create') }}" class="dashboard__link">Создать заявку</a></p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const showAllNotificationsBtn = document.getElementById('showAllNotifications');
        if (showAllNotificationsBtn) {
            showAllNotificationsBtn.addEventListener('click', function() {
                fetch('{{ route("api.notifications") }}')
                    .then(response => response.json())
                    .then(data => {
                        // Создаем модальное окно для отображения всех уведомлений
                        const modal = document.createElement('div');
                        modal.className = 'notification-modal';
                        
                        const modalContent = document.createElement('div');
                        modalContent.className = 'notification-modal__content';
                        
                        const modalHeader = document.createElement('div');
                        modalHeader.className = 'notification-modal__header';
                        modalHeader.innerHTML = `
                            <h2>Все уведомления</h2>
                            <button class="notification-modal__close">&times;</button>
                        `;
                        
                        const modalBody = document.createElement('div');
                        modalBody.className = 'notification-modal__body';
                        
                        // Если есть уведомления, выводим их
                        if (data.length > 0) {
                            let notificationsHtml = '';
                            data.forEach(notification => {
                                const notificationTypeClass = notification.type === 'important' 
                                    ? 'notification-item--important' 
                                    : (notification.type === 'warning' ? 'notification-item--warning' : '');
                                
                                let iconSvg = '';
                                if (notification.type === 'important') {
                                    iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
                                } else if (notification.type === 'warning') {
                                    iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
                                } else {
                                    iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>';
                                }
                                
                                const createdAt = new Date(notification.created_at);
                                const formattedDate = createdAt.toLocaleDateString('ru-RU') + ' ' + createdAt.toLocaleTimeString('ru-RU');
                                
                                notificationsHtml += `
                                    <div class="notification-item ${notificationTypeClass}">
                                        <div class="notification-item__icon">
                                            ${iconSvg}
                                        </div>
                                        <div class="notification-item__content">
                                            <div class="notification-item__title">${notification.title}</div>
                                            <div class="notification-item__text">${notification.message}</div>
                                            <div class="notification-item__date">${formattedDate}</div>
                                        </div>
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
                        modal.addEventListener('click', function(e) {
                            if (e.target === modal) {
                                document.body.removeChild(modal);
                            }
                        });
                    })
                    .catch(error => console.error('Ошибка при получении уведомлений:', error));
            });
        }
    });
</script>

<style>
/* Основные стили для личного кабинета */
.dashboard {
    padding: 20px 0;
}

.dashboard__header {
    margin-bottom: 30px;
}

.dashboard__title {
    font-size: 1.8rem;
    font-weight: 600;
    color: var(--color-black);
    margin: 0;
}

/* Стили для карточек */
.dashboard__cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.dashboard__card {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    border: 1px solid var(--color-light-gray);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.dashboard__card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

.dashboard__card-header {
    background-color: #f7f7f7;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--color-light-gray);
}

.dashboard__card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--color-black);
    margin: 0;
}

.dashboard__card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: var(--color-primary);
    color: white;
    border-radius: 50%;
}

.dashboard__card-content {
    padding: 20px;
}

/* Стили для профиля */
.profile__info {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.profile__row {
    display: flex;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 10px;
}

.profile__row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.profile__label {
    flex: 0 0 160px;
    font-weight: 500;
    color: #666;
}

.profile__value {
    flex: 1;
    color: var(--color-black);
    word-break: break-word;
}

/* Стили для статистики посещаемости */
.attendance-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.attendance-stats__item {
    flex: 1;
    min-width: calc(50% - 10px);
    padding: 15px;
    border-radius: 8px;
    color: white;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.attendance-stats__item--present {
    background-color: #4CAF50;
}

.attendance-stats__item--absent {
    background-color: #F44336;
}

.attendance-stats__item--vacation {
    background-color: #2196F3;
}

.attendance-stats__item--sick {
    background-color: #FF9800;
}

.attendance-stats__count {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 5px;
}

.attendance-stats__label {
    font-size: 14px;
    font-weight: 500;
}

.attendance-stats__footer {
    text-align: center;
    margin-top: 10px;
}

/* Стили для информации об отпуске */
.vacation-info {
    margin-bottom: 20px;
}

.vacation-info__item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.vacation-info__item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.vacation-info__label {
    font-weight: 500;
    color: #666;
}

.vacation-info__value {
    font-size: 18px;
    font-weight: 600;
    color: var(--color-black);
}

.vacation-info__buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Стили для кнопок */
.btn {
    display: inline-block;
    padding: 10px 15px;
    border-radius: 5px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
    text-align: center;
    border: none;
}

.btn--primary {
    background-color: var(--color-primary);
    color: white;
    flex: 1;
}

.btn--primary:hover {
    background-color: #e06328;
    transform: translateY(-2px);
}

.btn--secondary {
    background-color: #607D8B;
    color: white;
    flex: 1;
}

.btn--secondary:hover {
    background-color: #546E7A;
    transform: translateY(-2px);
}

/* Стили для секций */
.dashboard__section {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    border: 1px solid var(--color-light-gray);
    margin-bottom: 30px;
}

.dashboard__section-header {
    background-color: #f7f7f7;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--color-light-gray);
}

.dashboard__section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--color-black);
    margin: 0;
}

.dashboard__section-content {
    padding: 20px;
}

.dashboard__link {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.dashboard__link:hover {
    color: #e06328;
    text-decoration: underline;
}

.dashboard__view-all {
    font-size: 0.9rem;
}

.dashboard__empty-state {
    text-align: center;
    padding: 30px;
    color: #777;
}

/* Стили для карточек заявок */
.leave-requests-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.leave-request-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    border: 1px solid #eee;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.leave-request-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.leave-request-card__header {
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f9f9f9;
    border-bottom: 1px solid #eee;
}

.leave-request-card__type {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    color: white;
}

.leave-request-card__type--vacation {
    background-color: #2196F3;
}

.leave-request-card__type--sick {
    background-color: #FF9800;
}

.leave-request-card__status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
}

.leave-request-card__status--new {
    background-color: #FFC107;
    color: #000;
}

.leave-request-card__status--approved {
    background-color: #4CAF50;
    color: white;
}

.leave-request-card__status--rejected {
    background-color: #F44336;
    color: white;
}

.leave-request-card__content {
    padding: 15px;
}

.leave-request-card__row {
    margin-bottom: 10px;
}

.leave-request-card__row:last-child {
    margin-bottom: 0;
}

.leave-request-card__label {
    display: block;
    font-weight: 500;
    color: #666;
    margin-bottom: 3px;
}

.leave-request-card__value {
    display: block;
    color: var(--color-black);
}

.leave-request-card__footer {
    padding: 10px 15px;
    border-top: 1px solid #eee;
    background-color: #fafafa;
    font-size: 12px;
    color: #888;
    text-align: right;
}

/* Адаптивная верстка */
@media (max-width: 768px) {
    .dashboard__cards {
        grid-template-columns: 1fr;
    }
    
    .leave-requests-list {
        grid-template-columns: 1fr;
    }
    
    .attendance-stats__item {
        min-width: calc(50% - 10px);
    }
    
    .vacation-info__buttons {
        flex-direction: column;
    }
    
    .profile__row {
        flex-direction: column;
    }
    
    .profile__label {
        margin-bottom: 5px;
    }
}

/* Стили для уведомлений */
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notification-item {
    display: flex;
    padding: 12px 15px;
    background-color: #f9f9f9;
    border-radius: 8px;
    border-left: 3px solid #ccc;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
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

.notifications-footer {
    margin-top: 15px;
    text-align: center;
}

.notifications-footer__btn {
    background: none;
    border: none;
    color: var(--color-primary);
    font-weight: 500;
    cursor: pointer;
    transition: color 0.3s ease;
    padding: 5px 10px;
    text-decoration: none;
}

.notifications-footer__btn:hover {
    color:rgb(255, 255, 255);
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
</style>
@endsection 