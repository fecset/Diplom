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

    <div class="notifications-filters">
        <div class="notifications-filter-group">
            <label for="filterType" class="notifications-filter-label">Тип:</label>
            <select id="filterType" class="notifications-filter-select">
                <option value="">Все типы</option>
                <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Информационное</option>
                <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Предупреждение</option>
                <option value="important" {{ request('type') == 'important' ? 'selected' : '' }}>Важное</option>
            </select>
        </div>

        <div class="notifications-filter-group">
            <label for="filterStatus" class="notifications-filter-label">Статус:</label>
            <select id="filterStatus" class="notifications-filter-select">
                <option value="">Все статусы</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Неактивные</option>
            </select>
        </div>

        <div class="notifications-filter-group">
            <label for="filterGlobal" class="notifications-filter-label">Глобальное:</label>
            <select id="filterGlobal" class="notifications-filter-select">
                <option value="">Все</option>
                <option value="true" {{ request('is_global') == 'true' ? 'selected' : '' }}>Да</option>
                <option value="false" {{ request('is_global') == 'false' ? 'selected' : '' }}>Нет</option>
            </select>
        </div>

        <div class="notifications-filter-group">
            <label for="filterTitle" class="notifications-filter-label">Поиск по заголовку:</label>
            <input type="text" id="filterTitle" class="notifications-filter-input" placeholder="Введите заголовок..." value="{{ request('title') }}">
        </div>

        <a type="button" id="resetFilters" class="notifications-filter-reset">Сбросить</a>
    </div>

    <div id="noResultsMessage" class="notifications-no-results" style="display: none;">
        <p>Уведомления не найдены. Попробуйте изменить параметры поиска.</p>
    </div>

    <div class="notifications-table-container">
        <table class="notifications-table" id="notificationsTable">
            <thead>
                <tr>
                    <th data-sort="title">Заголовок <span class="notifications-sort-icon">↕</span></th>
                    <th data-sort="type">Тип <span class="notifications-sort-icon">↕</span></th>
                    <th>Статус</th>
                    <th>Глобальное</th>
                    <th data-sort="start_date">Период <span class="notifications-sort-icon">↕</span></th>
                    <th data-sort="created_at">Создано <span class="notifications-sort-icon">↕</span></th>
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
                        
                        <form method="POST" action="{{ route('notifications.destroy', $notification) }}" class="delete-form" data-notification-title="{{ $notification->title }}">
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

<script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initNotificationsFiltering();
    setupPaginationLinks();
    
    // Устанавливаем сортировку на основе данных из контроллера
    @if(isset($sortField) && isset($sortOrder))
    const sortField = '{{ $sortField }}';
    const sortOrder = '{{ $sortOrder }}';
    
    if (sortField && sortOrder) {
        const header = document.querySelector(`th[data-sort="${sortField}"]`);
        if (header) {
            const icon = header.querySelector('.notifications-sort-icon');
            icon.textContent = sortOrder === 'asc' ? '↑' : '↓';
            icon.classList.add('notifications-sort-icon--active');
            
            if (window.notificationsCurrentSort) {
                window.notificationsCurrentSort.field = sortField;
                window.notificationsCurrentSort.order = sortOrder;
            }
        }
    }
    @endif

    // Обработчики для удаления
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        const deleteButton = form.querySelector('.delete-icon');
        const notificationTitle = form.getAttribute('data-notification-title');
        
        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                if (confirm(`Вы уверены, что хотите удалить уведомление "${notificationTitle}"?`)) {
                    form.submit();
                }
            });
        }
    });
});

function initNotificationsFiltering() {
    const table = document.getElementById('notificationsTable');
    const rows = Array.from(table.querySelectorAll('tbody tr.notifications-row'));
    const filterType = document.getElementById('filterType');
    const filterStatus = document.getElementById('filterStatus');
    const filterGlobal = document.getElementById('filterGlobal');
    const filterTitle = document.getElementById('filterTitle');
    const resetButton = document.getElementById('resetFilters');
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    
    // Текущие параметры сортировки
    window.notificationsCurrentSort = {
        field: '{{ $sortField ?? 'created_at' }}',
        order: '{{ $sortOrder ?? 'desc' }}'
    };
    
    // Создаем массив данных для Fuse.js
    const searchData = rows.map(row => ({
        element: row,
        title: row.getAttribute('data-title'),
        type: row.getAttribute('data-type'),
        status: row.getAttribute('data-status'),
        isGlobal: row.getAttribute('data-is-global'),
        startDate: row.getAttribute('data-start-date'),
        endDate: row.getAttribute('data-end-date'),
        createdAt: row.getAttribute('data-created-at')
    }));
    
    // Настройки Fuse.js
    const fuseOptions = {
        keys: ['title'],
        threshold: 0.3,
        includeScore: true,
        shouldSort: false
    };
    
    const fuse = new Fuse(searchData, fuseOptions);
    
    function filterAndSortTable() {
        const typeFilter = filterType.value;
        const statusFilter = filterStatus.value;
        const globalFilter = filterGlobal.value;
        const titleFilter = filterTitle.value.trim().toLowerCase();
        
        // Управление пагинацией
        const paginationContainer = document.querySelector('.notifications-pagination');
        if (typeFilter || statusFilter || globalFilter || titleFilter) {
            if (paginationContainer) paginationContainer.style.display = 'none';
        } else {
            if (paginationContainer) paginationContainer.style.display = '';
        }
        
        // Скрываем все строки
        rows.forEach(row => row.style.display = 'none');
        
        // Фильтрация
        let filteredData = searchData;
        
        if (titleFilter) {
            const searchResults = fuse.search(titleFilter);
            filteredData = searchResults.map(result => result.item);
        }
        
        filteredData = filteredData.filter(item => {
            if (typeFilter && item.type !== typeFilter) return false;
            if (statusFilter && item.status !== statusFilter) return false;
            if (globalFilter && item.isGlobal !== globalFilter) return false;
            return true;
        });
        
        // Отображаем отфильтрованные строки
        const visibleRows = filteredData.map(item => item.element);
        visibleRows.forEach(row => row.style.display = '');
        
        // Сортировка
        visibleRows.sort((a, b) => {
            let aValue = a.getAttribute(`data-${window.notificationsCurrentSort.field}`);
            let bValue = b.getAttribute(`data-${window.notificationsCurrentSort.field}`);
            
            if (window.notificationsCurrentSort.field === 'created_at' || 
                window.notificationsCurrentSort.field === 'start_date' || 
                window.notificationsCurrentSort.field === 'end_date') {
                
                // Обрабатываем пустую строку или null как null перед созданием Date
                aValue = (aValue === null || aValue === '') ? null : aValue;
                bValue = (bValue === null || bValue === '') ? null : bValue;

                // Определяем даты для сравнения, обрабатывая null/пустые значения
                // При asc сортировке null идет первым (считаем его очень старой датой)
                // При desc сортировке null идет последним (считаем его очень новой датой)
                const dateA = aValue ? new Date(aValue).getTime() : (window.notificationsCurrentSort.order === 'asc' ? -Infinity : Infinity);
                const dateB = bValue ? new Date(bValue).getTime() : (window.notificationsCurrentSort.order === 'asc' ? -Infinity : Infinity);
                
                // Если даты невалидны, помещаем их в конец (для asc) или начало (для desc)
                if (isNaN(dateA) && isNaN(dateB)) return 0;
                if (isNaN(dateA)) return window.notificationsCurrentSort.order === 'asc' ? 1 : -1;
                if (isNaN(dateB)) return window.notificationsCurrentSort.order === 'asc' ? -1 : 1;

                return window.notificationsCurrentSort.order === 'asc' ? 
                    dateA - dateB : dateB - dateA;
            }
            
            return window.notificationsCurrentSort.order === 'asc' ? 
                aValue.localeCompare(bValue) : 
                bValue.localeCompare(aValue);
        });
        
        // Переупорядочиваем строки
        const tbody = table.querySelector('tbody');
        visibleRows.forEach(row => tbody.appendChild(row));
        
        // Обновляем индикаторы сортировки
        updateSortIcons();
        
        // Показываем сообщение, если ничего не найдено
        const noResults = document.getElementById('noResultsMessage');
        if (noResults) {
            noResults.style.display = visibleRows.length === 0 ? 'block' : 'none';
        }
    }
    
    // Обработчики событий для сортировки
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const field = this.getAttribute('data-sort');
            
            if (window.notificationsCurrentSort.field === field) {
                window.notificationsCurrentSort.order = 
                    window.notificationsCurrentSort.order === 'asc' ? 'desc' : 'asc';
            } else {
                window.notificationsCurrentSort.field = field;
                window.notificationsCurrentSort.order = 'asc';
            }
            
            filterAndSortTable();
        });
    });
    
    function updateSortIcons() {
        sortableHeaders.forEach(header => {
            const field = header.getAttribute('data-sort');
            const icon = header.querySelector('.notifications-sort-icon');
            
            if (field === window.notificationsCurrentSort.field) {
                icon.textContent = window.notificationsCurrentSort.order === 'asc' ? '↑' : '↓';
                icon.classList.add('notifications-sort-icon--active');
            } else {
                icon.textContent = '↕';
                icon.classList.remove('notifications-sort-icon--active');
            }
        });
    }
    
    // Привязываем обработчики событий
    filterType.addEventListener('change', filterAndSortTable);
    filterStatus.addEventListener('change', filterAndSortTable);
    filterGlobal.addEventListener('change', filterAndSortTable);
    
    let searchTimeout;
    filterTitle.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterAndSortTable, 300);
    });
    
    resetButton.addEventListener('click', function() {
        filterType.value = '';
        filterStatus.value = '';
        filterGlobal.value = '';
        filterTitle.value = '';
        filterAndSortTable();
    });
    
    // Запускаем первичную фильтрацию
    filterAndSortTable();
}

function setupPaginationLinks() {
    const paginationLinks = document.querySelectorAll('.notifications-pagination .pagination a');
    
    paginationLinks.forEach(link => {
        const url = new URL(link.href);
        if (window.notificationsCurrentSort) {
            url.searchParams.set('sort', window.notificationsCurrentSort.field);
            url.searchParams.set('order', window.notificationsCurrentSort.order);
        }
        link.href = url.toString();
    });
}
</script>

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