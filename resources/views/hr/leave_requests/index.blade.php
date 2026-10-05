@extends('layouts.app')
@section('title', 'Управление заявками на отпуск и больничный')

@section('content')
<div class="hr-leave-requests">
    <div class="hr-leave-requests__header">
        <h2 class="hr-leave-requests__title">Управление заявками</h2>
        <div class="hr-leave-requests__tabs">
            <a href="{{ route('hr.leave_requests.index') }}" class="hr-leave-requests__tab {{ !request('type') && !request('status') ? 'hr-leave-requests__tab--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                Все заявки
            </a>
            <a href="{{ route('hr.leave_requests.pending') }}" class="hr-leave-requests__tab {{ request('status') === 'new' ? 'hr-leave-requests__tab--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Ожидающие ({{ $requests->where('status', 'new')->count() }})
            </a>
            <a href="{{ route('hr.leave_requests.vacations') }}" class="hr-leave-requests__tab {{ request('type') === 'vacation' ? 'hr-leave-requests__tab--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-1.5"></path>
                    <path d="M12 12h9"></path>
                    <path d="M16 16l5-5"></path>
                    <path d="M16 8l5 5"></path>
                </svg>
                Отпуска
            </a>
            <a href="{{ route('hr.leave_requests.sick_leaves') }}" class="hr-leave-requests__tab {{ request('type') === 'sick_leave' ? 'hr-leave-requests__tab--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
                Больничные
            </a>
            <a href="{{ route('hr.leave_requests.business_trips') }}" class="hr-leave-requests__tab {{ request('type') === 'business_trip' ? 'hr-leave-requests__tab--active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 22v-4a4.97 4.97 0 0 0-1-3c1.09-.53 2-1.82 2-3a4.96 4.96 0 0 0-1.46-3.42V7c0-.66-.5-1-1-1H9c-.5 0-1 .34-1 1v.08A5 5 0 0 0 6 7c0 2.14 1.2 3.5 2 4l-1 3.5v4c0 1.1.9 2 2 2h2a2 2 0 0 0 2-2z"></path>
                </svg>
                Командировки
            </a>
        </div>
    </div>

    <div class="hr-leave-requests__filters">
        <form class="hr-leave-requests__filter-form">
            @if(request('type'))
                <input type="hidden" id="type" name="type" value="{{ request('type') }}">
            @endif
            @if(request('status'))
                <input type="hidden" id="status" name="status" value="{{ request('status') }}">
            @endif
            
            <div class="hr-leave-requests__filter-group">
                <label for="name" class="hr-leave-requests__filter-label">Имя сотрудника</label>
                <input type="text" id="name" name="name" class="hr-leave-requests__filter-input" value="{{ request('name') }}" placeholder="Введите имя">
            </div>
            
            <div class="hr-leave-requests__filter-group">
                <label for="department" class="hr-leave-requests__filter-label">Отдел</label>
                <select id="department" name="department" class="hr-leave-requests__filter-select">
                    <option value="">Все отделы</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="hr-leave-requests__filter-group">
                <label for="date_from" class="hr-leave-requests__filter-label">Дата начала</label>
                <input style="width: 90%;" type="date" id="date_from" name="date_from" class="hr-leave-requests__filter-input" value="{{ request('date_from') }}">
            </div>
            
            <div class="hr-leave-requests__filter-group">
                <label for="date_to" class="hr-leave-requests__filter-label">Дата окончания</label>
                <input style="width: 90%;" type="date" id="date_to" name="date_to" class="hr-leave-requests__filter-input" value="{{ request('date_to') }}">
            </div>
            
            <div class="hr-leave-requests__filter-actions">
                <a href="javascript:void(0)" id="resetFilters" class="hr-leave-requests__filter-reset">Сбросить</a>
            </div>
        </form>
    </div>

    <div class="hr-leave-requests__content">
        <div class="hr-leave-requests__table-wrapper">
            <table class="hr-leave-requests__table" id="leaveRequestsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Сотрудник</th>
                        <th>Отдел</th>
                        <th>Тип</th>
                        <th>Даты</th>
                        <th>Причина</th>
                        <th>Статус</th>
                        <th>Комментарий HR</th>
                        <th>Дата подачи</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                        <tr class="hr-leave-requests__row {{ $request->status === 'new' ? 'hr-leave-requests__row--new' : '' }}" 
                            data-id="{{ $request->id }}"
                            data-name="{{ $request->user->name }}"
                            data-department="{{ $request->user->department }}"
                            data-type="{{ $request->type }}"
                            data-date-start="{{ $request->date_start }}"
                            data-date-end="{{ $request->date_end }}"
                            data-status="{{ $request->status }}"
                            data-created-at="{{ $request->created_at->format('Y-m-d') }}">
                            <td>{{ $request->id }}</td>
                            <td>
                                <a href="{{ route('hr.personnel.show', $request->user) }}" class="hr-leave-requests__employee-link">
                                    {{ $request->user->name }}
                                </a>
                            </td>
                            <td>{{ $request->user->department?->name ?? '—' }}</td>
                            <td>
                                @if($request->type === 'vacation')
                                    <span class="hr-leave-requests__type hr-leave-requests__type--vacation">Отпуск</span>
                                @elseif($request->type === 'business_trip')
                                    <span class="hr-leave-requests__type hr-leave-requests__type--business_trip">Командировка</span>
                                @else
                                    <span class="hr-leave-requests__type hr-leave-requests__type--sick">Больничный</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($request->date_start)->format('d.m.Y') }} — {{ \Carbon\Carbon::parse($request->date_end)->format('d.m.Y') }}</td>
                            <td>{{ $request->reason ?? '—' }}</td>
                            <td>
                                @if($request->status === 'new')
                                    <span class="hr-leave-requests__status hr-leave-requests__status--new">Новая</span>
                                @elseif($request->status === 'approved')
                                    <span class="hr-leave-requests__status hr-leave-requests__status--approved">Одобрена</span>
                                @elseif($request->status === 'rejected')
                                    <span class="hr-leave-requests__status hr-leave-requests__status--rejected">Отклонена</span>
                                @endif
                            </td>
                            <td>{{ $request->hr_comment ?? '—' }}</td>
                            <td>{{ $request->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="hr-leave-requests__actions">
                                    <a href="{{ route('hr.leave_requests.show', $request) }}" class="hr-leave-requests__action-btn" title="Просмотр">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    
                                    @if($request->document_path)
                                    <a href="{{ asset('storage/' . $request->document_path) }}" target="_blank" class="hr-leave-requests__action-btn hr-leave-requests__action-btn--document" title="Просмотреть документ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                    </a>
                                    @endif
                                    
                                    @if($request->status === 'new')
                                        @if(Auth::user()->isAdmin() || $request->user_id !== Auth::id())
                                        <a href="#" onclick="event.preventDefault(); document.getElementById('approve-form-{{ $request->id }}').submit();" class="hr-leave-requests__action-btn hr-leave-requests__action-btn--approve" title="Одобрить">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </a>
                                        <form id="approve-form-{{ $request->id }}" action="{{ route('hr.leave_requests.update', $request) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                        </form>
                                        
                                        <a href="#" onclick="event.preventDefault(); document.getElementById('reject-form-{{ $request->id }}').submit();" class="hr-leave-requests__action-btn hr-leave-requests__action-btn--reject" title="Отклонить">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </a>
                                        <form id="reject-form-{{ $request->id }}" action="{{ route('hr.leave_requests.update', $request) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                        </form>
                                        @else
                                        <span class="hr-leave-requests__action-btn hr-leave-requests__action-btn--disabled" title="Вы не можете управлять своими заявками">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                            </svg>
                                        </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div id="noResultsMessage" style="display: none;" class="hr-leave-requests__empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <p>Заявок не найдено</p>
            <span>Попробуйте изменить параметры фильтрации или посмотреть все заявки</span>
        </div>
        
        <div class="hr-leave-requests__pagination" id="paginationContainer">
            {{ $requests->appends(request()->except('page'))->links('pagination.custom') }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Инициализация фильтрации заявок');
    initLeaveRequestsFiltering();
    setupPaginationLinks();
});

function initLeaveRequestsFiltering() {
    const table = document.getElementById('leaveRequestsTable');
    const rows = Array.from(table.querySelectorAll('tbody tr.hr-leave-requests__row'));
    const filterName = document.getElementById('name');
    const filterDepartment = document.getElementById('department');
    const filterDateFrom = document.getElementById('date_from');
    const filterDateTo = document.getElementById('date_to');
    const filterType = document.getElementById('type');
    const filterStatus = document.getElementById('status');
    const resetButton = document.getElementById('resetFilters');
    
    console.log('Найдено строк в таблице:', rows.length);
    
    // Создаем массив данных для Fuse.js
    const searchData = rows.map(row => {
        return {
            element: row,
            id: row.getAttribute('data-id'),
            name: row.getAttribute('data-name'),
            department: row.getAttribute('data-department'),
            type: row.getAttribute('data-type'),
            dateStart: row.getAttribute('data-date-start'),
            dateEnd: row.getAttribute('data-date-end'),
            status: row.getAttribute('data-status'),
            createdAt: row.getAttribute('data-created-at')
        };
    });
    
    // Настройки Fuse.js
    const fuseOptions = {
        keys: ['name'],
        threshold: 0.3,
        includeScore: true,
        shouldSort: false
    };
    
    // Инициализация Fuse.js
    const fuse = new Fuse(searchData, fuseOptions);
    
    // Функция фильтрации
    function filterTable() {
        const nameFilter = filterName.value.trim().toLowerCase();
        const departmentFilter = filterDepartment.value;
        const dateFromFilter = filterDateFrom.value;
        const dateToFilter = filterDateTo.value;
        const typeFilter = filterType ? filterType.value : '';
        const statusFilter = filterStatus ? filterStatus.value : '';
        
        console.log('Применение фильтров:', {
            name: nameFilter,
            department: departmentFilter,
            dateFrom: dateFromFilter,
            dateTo: dateToFilter,
            type: typeFilter,
            status: statusFilter
        });
        
        // Управление пагинацией - скрываем при активном фильтре
        const paginationContainer = document.getElementById('paginationContainer');
        if (nameFilter || departmentFilter || dateFromFilter || dateToFilter) {
            if (paginationContainer) paginationContainer.style.display = 'none';
        } else {
            if (paginationContainer) paginationContainer.style.display = '';
        }
        
        // Скрываем все строки перед фильтрацией
        rows.forEach(row => {
            row.style.display = 'none';
        });
        
        // Получаем результаты поиска
        let filteredData = searchData;
        
        // Применяем поиск по имени, если введен текст
        if (nameFilter) {
            const searchResults = fuse.search(nameFilter);
            filteredData = searchResults.map(result => result.item);
        }
        
        // Фильтруем по отделу
        if (departmentFilter) {
            filteredData = filteredData.filter(item => item.department === departmentFilter);
        }
        
        // Фильтруем по типу заявки
        if (typeFilter) {
            filteredData = filteredData.filter(item => item.type === typeFilter);
        }
        
        // Фильтруем по статусу
        if (statusFilter) {
            filteredData = filteredData.filter(item => item.status === statusFilter);
        }
        
        // Фильтруем по дате начала
        if (dateFromFilter) {
            filteredData = filteredData.filter(item => {
                return new Date(item.dateStart) >= new Date(dateFromFilter);
            });
        }
        
        // Фильтруем по дате окончания
        if (dateToFilter) {
            filteredData = filteredData.filter(item => {
                return new Date(item.dateEnd) <= new Date(dateToFilter);
            });
        }
        
        // Отображаем отфильтрованные строки
        const visibleRows = filteredData.map(item => item.element);
        
        console.log('Отображаем строк после фильтрации:', visibleRows.length);
        
        visibleRows.forEach(row => {
            row.style.display = '';
        });
        
        // Показываем сообщение, если ничего не найдено
        const noResults = document.getElementById('noResultsMessage');
        if (noResults) {
            if (visibleRows.length === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }
    }
    
    // Привязываем обработчики событий
    filterDepartment.addEventListener('change', filterTable);
    
    // Для полей с датами используем событие change
    filterDateFrom.addEventListener('change', filterTable);
    filterDateTo.addEventListener('change', filterTable);
    
    // Для поиска по имени используем debounce
    let searchTimeout;
    filterName.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterTable, 300);
    });
    
    // Сброс фильтров
    resetButton.addEventListener('click', function() {
        filterName.value = '';
        filterDepartment.value = '';
        filterDateFrom.value = '';
        filterDateTo.value = '';
        
        filterTable();
        
        console.log('Фильтры сброшены');
    });
    
    // Запускаем фильтрацию при загрузке
    filterTable();
}

function setupPaginationLinks() {
    // Получаем все ссылки пагинации
    const links = document.querySelectorAll('.pagination a');
    
    // Сохраняем текущие значения фильтров
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            // Получаем текущий URL ссылки
            let url = new URL(this.href);
            
            // Добавляем параметры фильтров к URL
            const name = document.getElementById('name').value;
            const department = document.getElementById('department').value;
            const dateFrom = document.getElementById('date_from').value;
            const dateTo = document.getElementById('date_to').value;
            
            if (name) url.searchParams.set('name', name);
            if (department) url.searchParams.set('department', department);
            if (dateFrom) url.searchParams.set('date_from', dateFrom);
            if (dateTo) url.searchParams.set('date_to', dateTo);
            
            // Обновляем ссылку
            this.href = url.toString();
        });
    });
}

// Функция debounce для отложенной обработки
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}
</script>

<style>
.hr-leave-requests {
    background: var(--color-white);
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(36,36,36,0.07);
    padding: 2rem 2.2rem 2.2rem 2.2rem;
    border: 1.5px solid var(--color-light-gray);
    margin-bottom: 2rem;
}

.hr-leave-requests__header {
    display: flex;
    flex-direction: column;
    margin-bottom: 1.5rem;
}

.hr-leave-requests__title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--color-black);
    margin-bottom: 1.2rem;
}

.hr-leave-requests__tabs {
    display: flex;
    gap: 0.5rem;
    border-bottom: 1px solid #eee;
    margin-bottom: 1.5rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
}

.hr-leave-requests__tab {
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

.hr-leave-requests__tab:hover {
    background-color: #f0f0f0;
    color: var(--color-black);
}

.hr-leave-requests__tab--active {
    background-color: var(--color-primary);
    color: white;
}

.hr-leave-requests__tab--active svg {
    color: white;
}

/* .hr-leave-requests__tab svg {
    color: #777;
} */

.hr-leave-requests__filters {
    background-color: #f9f9f9;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #eee;
}

.hr-leave-requests__filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
}

.hr-leave-requests__filter-group {
    flex: 1;
    min-width: 180px;
}

.hr-leave-requests__filter-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #555;
    font-size: 0.95rem;
}

.hr-leave-requests__filter-input,
.hr-leave-requests__filter-select {
    width: 100%;
    padding: calc(var(--spacing-unit)* 0.8) calc(var(--spacing-unit)* 0.75);
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    appearance: none;
    background-color: #fff;
}

.hr-leave-requests__filter-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23757575' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.7rem center;
    background-size: 12px;
    padding-right: 2rem;
}

.hr-leave-requests__filter-input:focus,
.hr-leave-requests__filter-select:focus {
    border-color: var(--color-primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(243, 112, 50, 0.15);
}

.hr-leave-requests__filter-actions {
    display: flex;
    gap: 0.7rem;
    align-items: center;
}

.hr-leave-requests__filter-reset {
    padding: 0.6rem 1rem;
    background-color: #f0f0f0;
    color: #555;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.hr-leave-requests__filter-reset:hover {
    background-color: #e0e0e0;
}

.hr-leave-requests__table-wrapper {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #eee;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
}

.hr-leave-requests__table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.95rem;
}

.hr-leave-requests__table th {
    background-color: #f7f7f7;
    font-weight: 600;
    color: #555;
    padding: 1rem 0.8rem;
    border-bottom: 1px solid #eee;

    white-space: nowrap;
}

.hr-leave-requests__table td {
    padding: 0.8rem;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.hr-leave-requests__table tr:last-child td {
    border-bottom: none;
}

.hr-leave-requests__table tbody tr {
    transition: background-color 0.2s ease;
}

.hr-leave-requests__table tbody tr:hover {
    background-color: #f8f8f8;
}

.hr-leave-requests__row--new {
    background-color: #fff9e6;
}

.hr-leave-requests__row--new:hover {
    background-color: #fff3d6 !important;
}

.hr-leave-requests__employee-link {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.hr-leave-requests__employee-link:hover {
    text-decoration: underline;
    color: var(--color-accent);
}

.hr-leave-requests__type {
    display: inline-block;
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9em;
    color: white;
}

.hr-leave-requests__type--vacation {
    background-color: #2196F3;
}

.hr-leave-requests__type--business_trip {
    background-color: #9C27B0;
}

.hr-leave-requests__type--sick {
    background-color: #FF9800;
}

.hr-leave-requests__status {
    display: inline-block;
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9em;
}

.hr-leave-requests__status--new {
    background-color: #FFC107;
    color: #000;
}

.hr-leave-requests__status--approved {
    background-color: #4CAF50;
    color: white;
}

.hr-leave-requests__status--rejected {
    background-color: #F44336;
    color: white;
}

.hr-leave-requests__actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.hr-leave-requests__action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background-color: #f0f0f0;
    color: #555;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.hr-leave-requests__action-btn:hover {
    background-color: #e0e0e0;
    transform: translateY(-2px);
}

.hr-leave-requests__action-btn--approve {
    background-color: #e8f5e9;
    color: #4CAF50;
}

.hr-leave-requests__action-btn--approve:hover {
    background-color: #c8e6c9;
}

.hr-leave-requests__action-btn--reject {
    background-color: #ffebee;
    color: #F44336;
}

.hr-leave-requests__action-btn--reject:hover {
    background-color: #ffcdd2;
}

.hr-leave-requests__action-btn--document {
    background-color: #e3f2fd;
    color: #2196F3;
}

.hr-leave-requests__action-btn--document:hover {
    background-color: #bbdefb;
}

.hr-leave-requests__action-btn--disabled {
    background-color: #f0f0f0;
    color: #aaa;
    cursor: not-allowed;
    opacity: 0.7;
}

.hr-leave-requests__action-btn--disabled:hover {
    background-color: #f0f0f0;
    transform: none;
    box-shadow: none;
}

.hr-leave-requests__quick-form {
    margin: 0;
    padding: 0;
}

.hr-leave-requests__empty {
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

.hr-leave-requests__empty svg {
    color: #aaa;
    margin-bottom: 1rem;
}

.hr-leave-requests__empty p {
    font-weight: 600;
    font-size: 1.1rem;
    margin: 0 0 0.5rem 0;
}

.hr-leave-requests__empty span {
    font-size: 0.95rem;
}

.hr-leave-requests__pagination {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

/* Адаптивная верстка */
@media (max-width: 900px) {
    .hr-leave-requests {
        padding: 1.5rem 1rem;
    }
    
    .hr-leave-requests__filter-form {
        flex-direction: column;
    }
    
    .hr-leave-requests__filter-group {
        width: 100%;
    }
    
    .hr-leave-requests__filter-actions {
        width: 100%;
    }
    
    .hr-leave-requests__filter-reset {
        flex: 1;
        text-align: center;
        justify-content: center;
    }
}
</style>
@endsection 