@extends('layouts.app')
@section('title', 'Кадровый учёт')

@section('content')

    <div class="personnel">
        <div class="personnel__header">
            <h2 class="personnel__title">Кадровый учёт</h2>
            <a href="{{ route('hr.personnel.create') }}" class="btn personnel__btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <span>Добавить сотрудника</span>
            </a>
        </div>
        
        <form method="GET" action="{{ route('hr.personnel.index') }}" class="personnel__filters">
            <div class="personnel__filter-group">
                <label for="filterDepartment" class="personnel__filter-label">Отдел:</label>
                <select id="filterDepartment" name="department" class="personnel__filter-select">
                    <option value="">Все отделы</option>

                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="personnel__filter-group">
                <label for="filterPosition" class="personnel__filter-label">Должность:</label>
                <select id="filterPosition" name="position" class="personnel__filter-select">
                    <option value="">Все должности</option>

                    @foreach($positions as $position)
                        <option value="{{ $position->id }}" @selected(request('position') == $position->id)>{{ $position->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="personnel__filter-group">
                <label for="filterName" class="personnel__filter-label">Поиск по имени:</label>
                <input type="text" id="filterName" name="name" value="{{ request('name') }}" class="personnel__filter-input" placeholder="Введите имя...">
            </div>
            
            <div class="original-filter-actions"><button type="submit" class="btn"><x-icon name="search"/>Найти</button><a href="{{ route('hr.personnel.index') }}" class="personnel__filter-reset">Сбросить</a></div>
        </form>
        
        <div id="noResultsMessage" class="personnel__no-results" style="display: none;">
            <p>Сотрудники не найдены. Попробуйте изменить параметры поиска.</p>
        </div>
        
        <div class="personnel__table-wrapper">
            <table class="personnel__table" id="personnelTable">
                <thead>
                    <tr>
                        <th><a href="{{ request()->url().'?'.http_build_query(array_merge(request()->except('page'), ['sort'=>'name','order'=>($sortField==='name' && $sortOrder==='asc')?'desc':'asc'])) }}">ФИО <span class="personnel__sort-icon">↕</span></a></th>
                        <th><a href="{{ request()->url().'?'.http_build_query(array_merge(request()->except('page'), ['sort'=>'position','order'=>($sortField==='position' && $sortOrder==='asc')?'desc':'asc'])) }}">Должность <span class="personnel__sort-icon">↕</span></a></th>
                        <th><a href="{{ request()->url().'?'.http_build_query(array_merge(request()->except('page'), ['sort'=>'department','order'=>($sortField==='department' && $sortOrder==='asc')?'desc':'asc'])) }}">Отдел <span class="personnel__sort-icon">↕</span></a></th>
                        <th>Логин</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th><a href="{{ request()->url().'?'.http_build_query(array_merge(request()->except('page'), ['sort'=>'role','order'=>($sortField==='role' && $sortOrder==='asc')?'desc':'asc'])) }}">Роль <span class="personnel__sort-icon">↕</span></a></th>
                        <th><a href="{{ request()->url().'?'.http_build_query(array_merge(request()->except('page'), ['sort'=>'hired_at','order'=>($sortField==='hired_at' && $sortOrder==='asc')?'desc':'asc'])) }}">Дата приёма <span class="personnel__sort-icon">↕</span></a></th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $user)
                    <tr class="personnel__data-row"
                        data-name="{{ strtolower($user->name) }}"
                        data-department="{{ $user->department?->id }}"
                        data-position="{{ $user->position?->id }}"
                        data-role="{{ $user->role }}"
                        data-hired_at="{{ $user->hired_at }}">
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->position?->name ?? '—' }}</td>
                        <td>{{ $user->department?->name ?? '—' }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->phone_number ?? '—' }}</td>
                        <td>{{ $user->email ?? '—' }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="personnel__role personnel__role--admin">Администратор</span>
                            @elseif($user->role === 'hr_specialist')
                                <span class="personnel__role personnel__role--hr">HR-специалист</span>
                            @else
                                <span class="personnel__role personnel__role--employee">Сотрудник</span>
                            @endif
                        </td>
                        <td>{{ $user->hired_at ? \Carbon\Carbon::parse($user->hired_at)->format('d.m.Y') : '—' }}</td>
                        <td>
                            <div class="personnel__actions">
                                <a href="{{ route('hr.personnel.show', $user) }}" class="personnel__action-btn" title="Просмотр">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                @if(!(Auth::user()->isHrSpecialist() && $user->isAdmin()))
                                <a href="{{ route('hr.personnel.edit', $user) }}" class="personnel__action-btn" title="Редактировать">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                                @endif
                                @if(auth()->user()->isAdmin() || auth()->user()->isHrSpecialist())
                                    <form action="{{ route('hr.personnel.delete', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены, что хотите удалить этого сотрудника?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="personnel__action-btn personnel__action-btn--danger" title="Удалить">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                              <path d="M3 6h18"/>
                                              <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                              <line x1="10" y1="11" x2="10" y2="17"/>
                                              <line x1="14" y1="11" x2="14" y2="17"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Пагинация -->
        <div class="personnel__pagination" id="paginationContainer">
            {{ $employees->links('pagination.custom') }}
        </div>
    </div>





<style>
.personnel {
    background: #fff;
    border-radius: 10px;
    border: 1.5px solid var(--color-light-gray);
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.personnel__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.personnel__title {
    font-size: 1.8rem;
    font-weight: 600;
    color: var(--color-black);
    margin: 0;
}

.personnel__btn {
    background-color: #F37032;
    color: white;
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

.personnel__btn:hover {
    background-color: #e06328;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(243, 112, 50, 0.3);
}

.personnel__btn svg {
    width: 18px;
    height: 18px;
}

.personnel__filters {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.personnel__filter-group {
    flex: 1;
    min-width: 200px;
    max-width: 300px;
}

.personnel__filter-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
}

.personnel__filter-select,
.personnel__filter-input {
    width: 100%;
    padding: calc(var(--spacing-unit)* 0.8) calc(var(--spacing-unit)* 0.75);
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    appearance: none;
    background-color: #fff;
}

.personnel__filter-select:focus,
.personnel__filter-input:focus {
    border-color: var(--color-primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(243, 112, 50, 0.2);
}

.personnel__filter-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23555' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 35px;
}

.personnel__filter-reset {
    padding: 0.6rem 1rem;
    background-color: #f0f0f0;
    color: #555;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.personnel__filter-reset:hover {
    background-color: #e0e0e0;
}

.personnel__no-results {
    text-align: center;
    padding: 30px;
    color: #777;
    background-color: #f9f9f9;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #eee;
}

.personnel__table-wrapper {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #eee;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.personnel__table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.personnel__table th,
.personnel__table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.personnel__table th {
    background-color: #f7f7f7;
    font-weight: 600;
    color: #555;
    cursor: pointer;
    white-space: nowrap;
    position: relative;
    padding-right: 25px;
}

.personnel__table th:hover {
    background-color: #f0f0f0;
}

.personnel__table tbody tr {
    transition: background-color 0.2s ease;
}

.personnel__table tbody tr:hover {
    background-color: #f8f8f8;
}

.personnel__table tbody tr:last-child td {
    border-bottom: none;
}

.personnel__sort-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    transition: color 0.3s ease;
}

.personnel__sort-icon--active {
    color: #F37032;
}

.personnel__role {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    min-width: 120px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border-left: 4px solid;
}

.alert-success {
    background-color: #e8f5e9;
    border-color: #4CAF50;
    color: #2E7D32;
}

.personnel__actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.personnel__action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    width: 36px;
    height: 36px;
    border-radius: 4px;
    background-color: #f5f5f5;
    transition: all 0.2s ease;
    text-decoration: none;
    border: 1px solid #e0e0e0;
    color: #555;
}

.personnel__action-btn:hover {
    background-color: #e0e0e0;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    color: #F37032;
}

.personnel__action-btn svg {
    width: 18px;
    height: 18px;
    display: inline-block;
}

.personnel__employee-link {
    color: #333;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
    display: inline-block;
    position: relative;
}

.personnel__employee-link:hover {
    color: #F37032;
}

.personnel__employee-link:after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -2px;
    left: 0;
    background-color: #F37032;
    transition: width 0.3s;
}

.personnel__employee-link:hover:after {
    width: 100%;
}

/* Стили для пагинации */
.personnel__pagination {
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

.btn--danger {
    background-color: #F44336 !important;
    color: #fff !important;
    border: none;
    transition: background 0.2s, color 0.2s;
}
.btn--danger:hover, .btn--danger:focus {
    background-color: #d32f2f !important;
    color: #fff !important;
}

.personnel__action-btn--danger {
    background-color: #F44336;
    color: #fff;
    border: 1px solid #F44336;
    display: inline-block;
    text-align: center;
    vertical-align: middle;
    padding: 0; /* если нужно */
    width: 36px; /* как у других action-кнопок */
    height: 36px;
    line-height: 40px; /* для вертикального выравнивания */
}
.personnel__action-btn--danger:hover, .personnel__action-btn--danger:focus {
    background-color: #d32f2f;
    color: #fff;
    border-color: #d32f2f;
}

.personnel__action-btn--danger svg {
    stroke: #fff;
}
</style>
@endsection 