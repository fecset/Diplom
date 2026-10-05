@extends('layouts.app')
@section('title', 'Информация о сотруднике: ' . $user->name)

@section('content')

    <div class="employee-profile">
        <div class="employee-profile__header">
            <h2 class="employee-profile__title">Информация о сотруднике</h2>
            <div class="employee-profile__actions">
                @if(!(Auth::user()->isHrSpecialist() && $user->isAdmin()))
                <a href="{{ route('hr.personnel.edit', $user) }}" class="btn employee-profile__edit-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Редактировать</span>
                </a>
                @endif
                <a href="{{ route('hr.personnel.index') }}" class="btn btn--gray">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Вернуться к списку</span>
                </a>
            </div>
        </div>
        
        <div class="employee-profile__content">
            <div class="employee-profile__main-info">
                <div class="employee-card">
                    <div class="employee-card__header">
                        <h3 class="employee-card__name">{{ $user->name }}</h3>
                        <div class="employee-card__role">
                            @if($user->role === 'admin')
                                <span class="personnel__role personnel__role--admin">Администратор</span>
                            @elseif($user->role === 'hr_specialist')
                                <span class="personnel__role personnel__role--hr">HR-специалист</span>
                            @else
                                <span class="personnel__role personnel__role--employee">Сотрудник</span>
                            @endif
                        </div>
                    </div>
                    <div class="employee-card__info">
                        <div class="employee-card__row">
                            <span class="employee-card__label">Должность:</span>
                            <span class="employee-card__value">{{ $user->position?->name ?? '—' }}</span>
                        </div>
                        <div class="employee-card__row">
                            <span class="employee-card__label">Отдел:</span>
                            <span class="employee-card__value">{{ $user->department?->name ?? '—' }}</span>
                        </div>
                        <div class="employee-card__row">
                            <span class="employee-card__label">Дата приема:</span>
                            <span class="employee-card__value">
                                {{ $user->hired_at ? \Carbon\Carbon::parse($user->hired_at)->format('d.m.Y') : '—' }}
                            </span>
                        </div>
                        <div class="employee-card__row">
                            <span class="employee-card__label">Трудовой стаж:</span>
                            <span class="employee-card__value">
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
                </div>
                
                <div class="employee-card">
                    <div class="employee-card__header">
                        <h3 class="employee-card__subtitle">Контактная информация</h3>
                    </div>
                    <div class="employee-card__info">
                        <div class="employee-card__row">
                            <span class="employee-card__label">Логин:</span>
                            <span class="employee-card__value">{{ $user->username }}</span>
                        </div>
                        <div class="employee-card__row">
                            <span class="employee-card__label">Email:</span>
                            <span class="employee-card__value">{{ $user->email ?? '—' }}</span>
                        </div>
                        <div class="employee-card__row">
                            <span class="employee-card__label">Телефон:</span>
                            <span class="employee-card__value">{{ $user->phone_number ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="employee-profile__stats">
                <div class="stats-card">
                    <div class="stats-card__header">
                        <h3 class="stats-card__title">Статистика посещаемости за текущий месяц</h3>
                    </div>
                    <div class="stats-card__content">
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
                        
                        <div class="stats-card__link-wrapper">
                            <a href="{{ route('hr.attendance.index', ['date' => \Carbon\Carbon::now()->format('Y-m'), 'name' => $user->name]) }}" class="stats-card__link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                    <line x1="9" y1="12" x2="15" y2="12"></line>
                                    <line x1="9" y1="16" x2="15" y2="16"></line>
                                </svg>
                                <span>Открыть в табеле</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                @if(count($leaveRequests) > 0)
                <div class="stats-card">
                    <div class="stats-card__header">
                        <h3 class="stats-card__title">Последние заявки</h3>
                    </div>
                    <div class="stats-card__content">
                        <div class="leave-requests">
                            @foreach($leaveRequests as $request)
                            <div class="leave-request">
                                <div class="leave-request__type {{ $request->type === 'vacation' ? 'leave-request__type--vacation' : 'leave-request__type--sick' }}">
                                    {{ $request->type === 'vacation' ? 'Отпуск' : 'Больничный' }}
                                </div>
                                <div class="leave-request__dates">
                                    {{ \Carbon\Carbon::parse($request->date_start)->format('d.m.Y') }} - 
                                    {{ \Carbon\Carbon::parse($request->date_end)->format('d.m.Y') }}
                                </div>
                                <div class="leave-request__status leave-request__status--{{ $request->status }}">
                                    @if($request->status === 'new')
                                        На рассмотрении
                                    @elseif($request->status === 'approved')
                                        Одобрена
                                    @elseif($request->status === 'rejected')
                                        Отклонена
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>



<style>


.employee-profile {
    background: #fff;
    border-radius: 10px;

    border: 1px solid #eee;
    padding: 30px;
    margin-bottom: 40px;
}

.employee-profile__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.employee-profile__title {
    font-size: 24px;
    font-weight: 600;
    color: var(--color-black);
    margin: 0;
}

.employee-profile__actions {
    display: flex;
    gap: 15px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 25px;
    border-radius: 5px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
}

.employee-profile__edit-btn {
    background-color: #F37032;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.employee-profile__edit-btn:hover {
    background-color: #e06328;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(243, 112, 50, 0.3);
}

.btn--gray {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

.btn--gray:hover {
    background-color: #e0e0e0;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.btn svg {
    width: 18px;
    height: 18px;
}

.employee-profile__content {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
}

.employee-profile__main-info {
    flex: 1;
    min-width: 350px;
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.employee-profile__stats {
    flex: 1;
    min-width: 350px;
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.employee-card, .stats-card {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    border: 1px solid #eee;
}

.employee-card__header, .stats-card__header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    background-color: #f9f9f9;
}

.employee-card__name {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin: 0 0 10px 0;
}

.employee-card__subtitle {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.stats-card__title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.employee-card__info {
    padding: 20px;
}

.employee-card__row {
    display: flex;
    margin-bottom: 15px;
    border-bottom: 1px solid #f5f5f5;
    padding-bottom: 15px;
}

.employee-card__row:last-child {
    margin-bottom: 0;
    border-bottom: none;
    padding-bottom: 0;
}

.employee-card__label {
    flex: 0 0 160px;
    color: #777;
    font-weight: 500;
}

.employee-card__value {
    flex: 1;
    color: #333;
}

.stats-card__content {
    padding: 20px;
}

.attendance-stats {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 20px;
}

.attendance-stats__item {
    flex: 1;
    text-align: center;
    padding: 15px;
    border-radius: 8px;
    color: white;
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
    margin-bottom: 8px;
}

.attendance-stats__label {
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-card__link-wrapper {
    text-align: center;
    margin-top: 15px;
}

.stats-card__link {
    color: #F37032;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    border: 1px solid #F37032;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.stats-card__link:hover {
    background-color: #F37032;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(243, 112, 50, 0.3);
}

.stats-card__link svg {
    width: 16px;
    height: 16px;
}

.leave-requests {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.leave-request {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-radius: 5px;
    background-color: #f9f9f9;
    border: 1px solid #eee;
}

.leave-request__type {
    font-weight: 500;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    color: white;
}

.leave-request__type--vacation {
    background-color: #2196F3;
}

.leave-request__type--sick {
    background-color: #FF9800;
}

.leave-request__dates {
    font-size: 14px;
    color: #555;
    font-weight: 500;
}

.leave-request__status {
    font-size: 14px;
    padding: 6px 12px;
    border-radius: 4px;
    font-weight: 500;
}

.leave-request__status--pending {
    background-color: #FFC107;
    color: #000;
}

.leave-request__status--new {
    background-color: #FFC107;
    color: #000;
}

.leave-request__status--approved {
    background-color: #4CAF50;
    color: white;
}

.leave-request__status--rejected {
    background-color: #F44336;
    color: white;
}

.personnel__role {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
}

.personnel__role--admin {
    background-color: #673AB7;
    color: white;
}

.personnel__role--hr {
    background-color: #009688;
    color: white;
}

.personnel__role--employee {
    background-color: #607D8B;
    color: white;
}

@media (max-width: 768px) {
    .employee-profile__content {
        flex-direction: column;
    }
    
    .employee-profile__main-info,
    .employee-profile__stats {
        min-width: 100%;
    }
    
    .employee-card__row {
        flex-direction: column;
    }
    
    .employee-card__label {
        margin-bottom: 5px;
    }
    
    .attendance-stats {
        flex-wrap: wrap;
    }
    
    .attendance-stats__item {
        min-width: calc(50% - 10px);
        margin-bottom: 10px;
    }
}
</style>
@endsection 