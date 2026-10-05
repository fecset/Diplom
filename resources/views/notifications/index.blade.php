@extends('layouts.app')
@section('title', 'Управление уведомлениями')

@section('content')
<div class="notifications-container">
    <div class="notifications-header">
        <h1 class="notifications-title">Управление уведомлениями</h1>
        <a href="{{ route('notifications.create') }}" class="notifications-create-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <span>Создать уведомление</span>
        </a>
    </div>

    <form method="GET" class="notifications-filters">
        <div class="notifications-filter-group">
            <label for="filterType" class="notifications-filter-label">Тип:</label>
            <select id="filterType" name="type" class="notifications-filter-select">
                <option value="">Все типы</option>
                <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Информационное</option>
                <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Предупреждение</option>
                <option value="important" {{ request('type') == 'important' ? 'selected' : '' }}>Важное</option>
            </select>
        </div>

        <div class="notifications-filter-group">
            <label for="filterStatus" class="notifications-filter-label">Статус:</label>
            <select id="filterStatus" name="status" class="notifications-filter-select">
                <option value="">Все статусы</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивные</option>
            </select>
        </div>

        <div class="notifications-filter-group">
            <label for="filterGlobal" class="notifications-filter-label">Глобальное:</label>
            <select id="filterGlobal" name="is_global" class="notifications-filter-select">
                <option value="">Все</option>
                <option value="true" {{ request('is_global') == 'true' ? 'selected' : '' }}>Да</option>
                <option value="false" {{ request('is_global') == 'false' ? 'selected' : '' }}>Нет</option>
            </select>
        </div>

        <div class="notifications-filter-group">
            <label for="filterTitle" class="notifications-filter-label">Поиск по заголовку:</label>
            <input type="text" id="filterTitle" name="title" class="notifications-filter-input" placeholder="Введите заголовок..." value="{{ request('title') }}">
        </div>

        <button type="submit" class="btn">Найти</button><a href="{{ route('notifications.index') }}" class="notifications-filter-reset">Сбросить</a>
    </form>

    <div id="noResultsMessage" class="notifications-no-results" style="display: none;">
        <p>Уведомления не найдены. Попробуйте изменить параметры поиска.</p>
    </div>

    <div class="notifications-table-container">
        <table class="notifications-table" id="notificationsTable">
            <thead>
                <tr>
                    <th><a href="{{ request()->url().'?'.http_build_query(array_merge(request()->except('page'), ['sort'=>'title','order'=>($sortField==='title' && $sortOrder==='asc')?'desc':'asc'])) }}">Заголовок <span class="notifications-sort-icon">↕</span></a></th>
                    <th><a href="{{ request()->url().'?'.http_build_query(array_merge(request()->except('page'), ['sort'=>'type','order'=>($sortField==='type' && $sortOrder==='asc')?'desc':'asc'])) }}">Тип <span class="notifications-sort-icon">↕</span></a></th>
                    <th>Статус</th>
                    <th>Глобальное</th>
                    <th><a href="{{ request()->url().'?'.http_build_query(array_merge(request()->except('page'), ['sort'=>'start_date','order'=>($sortField==='start_date' && $sortOrder==='asc')?'desc':'asc'])) }}">Период <span class="notifications-sort-icon">↕</span></a></th>
                    <th><a href="{{ request()->url().'?'.http_build_query(array_merge(request()->except('page'), ['sort'=>'created_at','order'=>($sortField==='created_at' && $sortOrder==='asc')?'desc':'asc'])) }}">Создано <span class="notifications-sort-icon">↕</span></a></th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                <tr class="notifications-row" 
                    data-title="{{ strtolower($notification->title) }}"
                    data-type="{{ $notification->type }}"
                    data-status="{{ $notification->isActive() ? 'active' : 'inactive' }}"
                    data-is-global="{{ $notification->is_global ? 'true' : 'false' }}"
                    data-start-date="{{ $notification->start_date ? \Carbon\Carbon::parse($notification->start_date)->toISOString() : '' }}"
                    data-end-date="{{ $notification->end_date ? \Carbon\Carbon::parse($notification->end_date)->toISOString() : '' }}"
                    data-created-at="{{ $notification->created_at ? \Carbon\Carbon::parse($notification->created_at)->toISOString() : '' }}">
                    <td>{{ $notification->title }}</td>
                    <td>
                        @if($notification->type == 'info')
                            <span class="badge badge-info">Информационное</span>
                        @elseif($notification->type == 'warning')
                            <span class="badge badge-warning">Предупреждение</span>
                        @elseif($notification->type == 'important')
                            <span class="badge badge-danger">Важное</span>
                        @endif
                    </td>
                    <td>
                        @if($notification->isActive())
                            <span class="badge badge-success">Активно</span>
                        @else
                            <span class="badge badge-secondary">Неактивно</span>
                        @endif
                    </td>
                    <td>
                        @if($notification->is_global)
                            <span class="badge badge-primary">Да</span>
                        @else
                            <span class="badge badge-secondary">Нет</span>
                        @endif
                    </td>
                    <td>
                        @if($notification->start_date && $notification->end_date)
                            {{ \Carbon\Carbon::parse($notification->start_date)->format('d.m.Y') }} - 
                            {{ \Carbon\Carbon::parse($notification->end_date)->format('d.m.Y') }}
                        @elseif($notification->start_date)
                            С {{ \Carbon\Carbon::parse($notification->start_date)->format('d.m.Y') }}
                        @elseif($notification->end_date)
                            До {{ \Carbon\Carbon::parse($notification->end_date)->format('d.m.Y') }}
                        @else
                            Бессрочно
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($notification->created_at)->format('d.m.Y H:i') }}</td>
                    <td class="notifications-actions">
                        <a href="{{ route('notifications.edit', $notification) }}" class="action-icon edit-icon" title="Изменить">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </a>
                        
                        <form method="POST" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Удалить уведомление?')" class="delete-form" data-notification-title="{{ $notification->title }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="action-icon delete-icon" title="Удалить">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Уведомления не найдены</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(!$showAll)
    <div class="notifications-pagination">
        {{ $notifications->links('pagination.custom') }}
    </div>
    @endif
</div>




<style>
/* Основные стили контейнера и заголовка остаются без изменений */
.notifications-container {
    background: #fff;
    border-radius: 10px;
    border: 1.5px solid var(--color-light-gray);
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

/* Стили для фильтров */
.notifications-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.notifications-filter-group {
    flex: 1;
    min-width: 200px;
    max-width: 300px;
}

.notifications-filter-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
}

.notifications-filter-select,
.notifications-filter-input {
    width: 100%;
    padding: calc(var(--spacing-unit)* 0.8) calc(var(--spacing-unit)* 0.75);
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    appearance: none;
    background-color: #fff;
}

.notifications-filter-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23555' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 35px;
}

.notifications-filter-select:focus,
.notifications-filter-input:focus {
    border-color: var(--color-primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(243, 112, 50, 0.2);
}

.notifications-filter-reset {
    padding: 0.6rem 1rem;
    background-color: #f0f0f0;
    color: #555;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.notifications-filter-reset:hover {
    background-color: #e0e0e0;
}

/* Стили для таблицы */
.notifications-table-container {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #eee;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.notifications-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.notifications-table th,
.notifications-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.notifications-table th {
    background-color: #f7f7f7;
    font-weight: 600;
    color: #555;
    cursor: pointer;
    white-space: nowrap;
    position: relative;
    padding-right: 25px;
}

.notifications-table th:hover {
    background-color: #f0f0f0;
}

.notifications-table tbody tr {
    transition: background-color 0.2s ease;
}

.notifications-table tbody tr:hover {
    background-color: #f8f8f8;
}

.notifications-sort-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    transition: color 0.3s ease;
}

.notifications-sort-icon--active {
    color: var(--color-primary);
}

/* Стили для пагинации */
.notifications-pagination {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}

.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 8px;
}

.page-item {
    display: inline-block;
}

.page-item a, .page-item span {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border-radius: 5px;
    text-decoration: none;
    color: #555;
    background-color: #f7f7f7;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid #e0e0e0;
}

.page-item.active span {
    background-color: #F37032;
    color: white;
    border-color: #F37032;
    box-shadow: 0 2px 5px rgba(243, 112, 50, 0.3);
}

.page-item a:hover {
    background-color: #e0e0e0;
    transform: translateY(-2px);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

.page-item.disabled span {
    color: #aaa;
    cursor: not-allowed;
    background-color: #f5f5f5;
    border-color: #e0e0e0;
    box-shadow: none;
    opacity: 0.7;
}

/* Стили для сообщения об отсутствии результатов */
.notifications-no-results {
    text-align: center;
    padding: 30px;
    color: #777;
    background-color: #f9f9f9;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #eee;
}

/* Адаптивность */
@media (max-width: 768px) {
    .notifications-filters {
        flex-direction: column;
        gap: 15px;
    }
    
    .notifications-filter-group {
        width: 100%;
        max-width: none;
    }
    
    .notifications-filter-reset {
        width: 100%;
        text-align: center;
    }
    
    .notifications-table th,
    .notifications-table td {
        padding: 10px;
        font-size: 14px;
    }
    
    .notifications-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .notifications-create-btn {
        width: 100%;
        justify-content: center;
    }
}

/* Кнопка создания уведомления */
.notifications-create-btn {
    background-color: #F37032;
    color: #fff;
    border: none;
    padding: 12px 25px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(243, 112, 50, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.notifications-create-btn:hover {
    background-color: #e06328;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(243, 112, 50, 0.3);
}
.notifications-create-btn svg {
    width: 18px;
    height: 18px;
}

/* Кнопки действий (редактирование и удаление) */
.notifications-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    align-items: center;
}
.action-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 4px;
    background-color: #f5f5f5;
    transition: all 0.2s ease;
    text-decoration: none;
    border: 1px solid #e0e0e0;
    color: #555;
    cursor: pointer;
    padding: 0;
}
.edit-icon {
    color: #555;
}
.edit-icon:hover {
    background-color: #e0e0e0;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    color: #F37032;
}
.delete-icon {
    background-color: #F37032;
    color: #fff;
    border: 1px solid #F37032;
}
.delete-icon:hover {
    background-color: #d32f2f;
    color: #fff;
    border-color: #d32f2f;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}
.delete-icon svg {
    stroke: #fff;
}

/* Цветные бейджи для типа и статуса */
.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    min-width: 90px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.badge-info {
    background-color: #2196F3;
    color: #fff;
}
.badge-warning {
    background-color: #FF9800;
    color: #fff;
}
.badge-danger {
    background-color: #F44336;
    color: #fff;
}
.badge-success {
    background-color: #4CAF50;
    color: #fff;
}
.badge-secondary {
    background-color: #9E9E9E;
    color: #fff;
}
.badge-primary {
    background-color: #F37032;
    color: #fff;
}
</style>
@endsection 