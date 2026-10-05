@extends('layouts.app')
@section('title', match(isset($type) ? $type : null) {
    'sick_leave' => 'Мои больничные',
    'business_trip' => 'Мои командировки',
    default => 'Мои заявки',
})

@section('content')
<div class="requests">
    <div class="requests__header">
        <h2 class="requests__title">
            @if(isset($type))
                @if($type === 'sick_leave')
                    Мои заявки на больничный
                @elseif($type === 'business_trip')
                    Мои заявки на командировку
                @else
                    Мои заявки на отпуск
                @endif
            @else
                Все мои заявки
            @endif
        </h2>
        <div class="requests__actions">
            <a href="{{ route('leave_requests.create', ['type' => 'vacation']) }}" class="requests__btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-1.5"></path>
                    <path d="M12 12h9"></path>
                    <path d="M16 16l5-5"></path>
                    <path d="M16 8l5 5"></path>
                </svg>
                Подать заявку на отпуск
            </a>
            <a href="{{ route('leave_requests.create', ['type' => 'sick_leave']) }}" class="requests__btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
                Подать заявку на больничный
            </a>
             <a href="{{ route('leave_requests.create', ['type' => 'business_trip']) }}" class="requests__btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 22v-4a4.97 4.97 0 0 0-1-3c1.09-.53 2-1.82 2-3a4.96 4.96 0 0 0-1.46-3.42V7c0-.66-.5-1-1-1H9c-.5 0-1 .34-1 1v.08A5 5 0 0 0 6 7c0 2.14 1.2 3.5 2 4l-1 3.5v4c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2z"></path></svg>
                Подать заявку на командировку
            </a>
        </div>
    </div>

    
    <div class="requests__tabs">
        <a href="{{ route('leave_requests.index') }}" class="requests__tab {{ !isset($type) ? 'requests__tab--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            Все заявки
        </a>
        <a href="{{ route('leave_requests.vacation') }}" class="requests__tab {{ isset($type) && $type === 'vacation' ? 'requests__tab--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-1.5"></path>
                <path d="M12 12h9"></path>
                <path d="M16 16l5-5"></path>
                <path d="M16 8l5 5"></path>
            </svg>
            Отпуска
        </a>
        <a href="{{ route('leave_requests.sick_leave') }}" class="requests__tab {{ isset($type) && $type === 'sick_leave' ? 'requests__tab--active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
            Больничные
        </a>
        <a href="{{ route('leave_requests.business_trip') }}" class="requests__tab {{ isset($type) && $type === 'business_trip' ? 'requests__tab--active' : '' }}">
             <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 22v-4a4.97 4.97 0 0 0-1-3c1.09-.53 2-1.82 2-3a4.96 4.96 0 0 0-1.46-3.42V7c0-.66-.5-1-1-1H9c-.5 0-1 .34-1 1v.08A5 5 0 0 0 6 7c0 2.14 1.2 3.5 2 4l-1 3.5v4c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2z"></path></svg>
             Командировки
        </a>
    </div>
    
    <div class="requests__content">
        @if($requests->count() > 0)
            <div class="requests__table-wrap">
                <table class="requests__table">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>Тип</th>
                            <th>Период</th>
                            @if(isset($type) && $type === 'business_trip')
                                <th>Место</th>
                                <th>Цель</th>
                            @else
                                <th>Дней</th>
                                <th>Причина</th>
                            @endif
                            <th>Статус</th>
                            <th>Комментарий HR</th>
                            <th>Дата подачи</th>
                            <th>Документ</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($requests as $req)
                        <tr class="requests__row {{ $req->status === 'new' ? 'requests__row--new' : ($req->status === 'approved' ? 'requests__row--approved' : ($req->status === 'rejected' ? 'requests__row--rejected' : '')) }}">
                            <td>{{ $req->id }}</td>
                            <td>
                                @if($req->type === 'vacation')
                                    <span class="requests__type requests__type--vacation">Отпуск</span>
                                @elseif($req->type === 'sick_leave')
                                    <span class="requests__type requests__type--sick">Больничный</span>
                                @elseif($req->type === 'business_trip')
                                     <span class="requests__type requests__type--business_trip">Командировка</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($req->date_start)->format('d.m.Y') }} — {{ \Carbon\Carbon::parse($req->date_end)->format('d.m.Y') }}</td>
                             @if(isset($type) && $type === 'business_trip')
                                <td>{{ $req->destination ?? '—' }}</td>
                                <td>{{ $req->purpose ?? '—' }}</td>
                            @else
                                <td>{{ \Carbon\Carbon::parse($req->date_start)->diffInDays(\Carbon\Carbon::parse($req->date_end)) + 1 }}</td>
                                <td>{{ $req->reason ?? '—' }}</td>
                            @endif
                            <td>
                                @if($req->status === 'new')
                                    <span class="requests__status requests__status--new">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        На рассмотрении
                                    </span>
                                @elseif($req->status === 'approved')
                                    <span class="requests__status requests__status--approved">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                        Одобрена
                                    </span>
                                @elseif($req->status === 'rejected')
                                    <span class="requests__status requests__status--rejected">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                        Отклонена
                                    </span>
                                @endif
                            </td>
                            <td>{{ $req->hr_comment ?? '—' }}</td>
                            <td>{{ $req->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                @if($req->document_path)
                                <a href="{{ asset('storage/' . $req->document_path) }}" target="_blank" class="requests__document-link" title="Просмотреть документ">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="requests__empty">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <p>У вас пока нет заявок</p>

            </div>
        @endif
    </div>
</div>

<style>
.requests {
    background: var(--color-white);
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(36,36,36,0.07);
    padding: 2rem 2.2rem 2.2rem 2.2rem;
    border: 1.5px solid var(--color-light-gray);
    margin-bottom: 2rem;
}

.requests__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.requests__title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--color-black);
    margin: 0;
}

.requests__actions {
    display: flex;
    gap: 1rem;
}

.requests__btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0.6rem 1.2rem;
    background-color: var(--color-primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.requests__btn:hover {
    background-color: var(--color-accent);
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(243, 112, 50, 0.3);
}

.requests__tabs {
    display: flex;
    gap: 0.5rem;
    border-bottom: 1px solid #eee;
    margin-bottom: 1.5rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
}

.requests__tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0.7rem 1.2rem;
    border-radius: 8px;
    color: #555;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.requests__tab:hover {
    background-color: #f0f0f0;
    color: var(--color-black);
}

.requests__tab--active {
    background-color: var(--color-primary);
    color: white;
}

.requests__tab--active svg {
    color: white;
}

/* .requests__tab svg {
    color: #777;
} */

.requests__table-wrap {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #eee;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.requests__table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.95rem;
}

.requests__table th {
    background-color: #f7f7f7;
    font-weight: 600;
    color: #555;
    padding: 1rem 0.8rem;
    border-bottom: 1px solid #eee;
    text-align: left;
    white-space: nowrap;
}

.requests__table td {
    padding: 0.8rem;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.requests__table tr:last-child td {
    border-bottom: none;
}

.requests__table tbody tr {
    transition: background-color 0.2s ease;
}

.requests__table tbody tr:hover {
    background-color: #f8f8f8;
}

.requests__row--new {
    background-color: #fff9e6;
}

.requests__row--approved {
    background-color: #f1f8e9;
}

.requests__row--rejected {
    background-color: #ffebee;
}

.requests__row--new:hover {
    background-color: #fff3d6 !important;
}

.requests__row--approved:hover {
    background-color: #e8f5e9 !important;
}

.requests__row--rejected:hover {
    background-color: #fee0e0 !important;
}

.requests__type {
    display: inline-block;
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9em;
    color: white;
}

.requests__type--vacation {
    background-color: #2196F3;
}

.requests__type--sick {
    background-color: #FF9800;
}

.requests__type--business_trip {
    background-color: #9C27B0;
}

.requests__status {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9em;
    white-space: nowrap;
}

.requests__status--new {
    background-color: #FFC107;
    color: #000;
}

.requests__status--approved {
    background-color: #4CAF50;
    color: white;
}

.requests__status--rejected {
    background-color: #F44336;
    color: white;
}

.requests__document-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background-color: #f0f0f0;
    color: #555;
    transition: all 0.2s ease;
}

.requests__document-link:hover {
    background-color: var(--color-primary);
    color: white;
    transform: translateY(-2px);
}

.requests__empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    text-align: center;
    color: #777;
    background-color: #f9f9f9;
    border-radius: 10px;
    border: 1px dashed #ddd;
}


.requests__empty p {
    font-weight: 600;
    font-size: 1.1rem;
    margin: 0 0 1.5rem 0;
}

.requests__empty-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0.7rem 1.5rem;
    background-color: var(--color-primary);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.requests__empty-btn:hover {
    background-color: var(--color-accent);
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(243, 112, 50, 0.3);
}

/* Адаптивная верстка */
@media (max-width: 900px) {
    .requests {
        padding: 1.5rem 1rem;
    }
    
    .requests__header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .requests__actions {
        width: 100%;
        flex-direction: column;
    }
    
    .requests__btn {
        width: 100%;
        justify-content: center;
    }
}

/* Стили для иконок вкладок */
.requests__tab svg {
    color: #777;
}

.requests__tab--active svg {
    color: white;
}
</style>
@endsection 