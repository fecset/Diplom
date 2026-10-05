@extends('layouts.app')
@section('title', 'Табель учёта рабочего времени')

@php
$special_container = true;
@endphp

@section('content')
<div class="attendance">
    <div class="attendance__header">
        <h2 class="attendance__title">Табель учёта рабочего времени</h2>
        <form method="GET" class="attendance__period-form">
            <label for="period" class="attendance__period-label">Период:</label>
            <input type="month" id="period" name="date" value="{{ $start->format('Y-m') }}" class="attendance__period-input">
            <button type="submit" class="btn attendance__period-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>Показать</span>
            </button>
        </form>
    </div>

    <!-- Фильтры -->
    <div class="attendance__filters">
        @if(Auth::user()->isAdmin() || Auth::user()->isHrSpecialist())
        <div class="attendance__filter-group">
            <label for="filterDepartment" class="attendance__filter-label">Отдел:</label>
            <select id="filterDepartment" class="attendance__filter-select">
                <option value="">Все отделы</option>
                @php 
                    $departments = $users->pluck('department')->filter()->unique('id')->sortBy('name')->values();
                @endphp
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="attendance__filter-group">
            <label for="filterPosition" class="attendance__filter-label">Должность:</label>
            <select id="filterPosition" class="attendance__filter-select">
                <option value="">Все должности</option>
                @php 
                    $positions = $users->pluck('position')->filter()->unique('id')->sortBy('name')->values();
                @endphp
                @foreach($positions as $position)
                    <option value="{{ $position->id }}">{{ $position->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="attendance__filter-group">
            <label for="filterName" class="attendance__filter-label">Поиск по имени:</label>
            <input type="text" id="filterName" class="attendance__filter-input" placeholder="Введите имя...">
        </div>
        <button type="button" id="resetFilters" class="attendance__filter-reset">Сбросить</button>
    </div>

    <div id="noResultsMessage" class="attendance__no-results" style="display: none;">
        <p>Сотрудники не найдены. Попробуйте изменить параметры поиска.</p>
    </div>

    <div class="attendance__table-outer" id="attendance-scroll-container">
        <table class="attendance__table" id="attendanceTable">
            <thead>
                <tr>
                    <th class="attendance__sticky-col attendance__sticky-col--name" data-pos="0" data-sort="name">Сотрудник <span class="attendance__sort-icon">↕</span></th>
                    <th class="attendance__sticky-col attendance__sticky-col--pos" data-pos="1" data-sort="position">Должность <span class="attendance__sort-icon">↕</span></th>
                    <th class="attendance__sticky-col attendance__sticky-col--dep" data-pos="2" data-sort="department">Отдел <span class="attendance__sort-icon">↕</span></th>
                    @for($d = $start->copy(); $d <= $end; $d->addDay())
                        <th>{{ $d->format('d') }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr class="attendance__data-row" 
                    data-name="{{ strtolower($user->name) }}" 
                    data-full-name="{{ $user->name }}"
                    data-department="{{ $user->department?->id }}" 
                    data-position="{{ $user->position?->id }}">
                    <td class="attendance__sticky-col attendance__sticky-col--name" data-pos="0">{{ $user->name }}</td>
                    <td class="attendance__sticky-col attendance__sticky-col--pos" data-pos="1">{{ $user->position?->name ?? '—' }}</td>
                    <td class="attendance__sticky-col attendance__sticky-col--dep" data-pos="2">{{ $user->department?->name ?? '—' }}</td>
                    @php $d = $start->copy(); @endphp
                    @while($d <= $end)
                        @php
                            $att = $attendances[$user->id][$d->format('Y-m-d')][0] ?? null;
                            $status = $att->status ?? null;
                            $comment = $att->comment ?? null;
                            $cellClass = $status ? "attendance__cell--{$status}" : '';
                            $commentClass = $comment ? "attendance__cell--with-comment" : '';
                        @endphp
                        <td class="{{ $cellClass }} {{ $commentClass }}">
                            @if(Auth::user()->isAdmin() || Auth::user()->isHrSpecialist())
                                <form method="POST" action="{{ route('hr.attendance.store') }}" class="attendance__cell-form">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <input type="hidden" name="date" value="{{ $d->format('Y-m-d') }}">
                                    <select name="status" class="attendance__status-select">
                                        <option value="present" @selected($status==='present')>Явка</option>
                                        <option value="absent" @selected($status==='absent')>Неявка</option>
                                        <option value="vacation" @selected($status==='vacation')>Отпуск</option>
                                        <option value="sick_leave" @selected($status==='sick_leave')>Больничный</option>
                                    </select>
                                    <input type="text" name="comment" value="{{ $comment }}" placeholder="Комментарий" class="attendance__comment-input">
                                    <button type="submit" class="btn btn--gray attendance__save-btn" title="Сохранить">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                            <polyline points="7 3 7 8 15 8"></polyline>
                                        </svg>
                                    </button>
                                </form>
                            @else
                                @if($status === 'present')
                                    <span class="attendance__status attendance__status--present">Я</span>
                                @elseif($status === 'absent')
                                    <span class="attendance__status attendance__status--absent">Н</span>
                                @elseif($status === 'vacation')
                                    <span class="attendance__status attendance__status--vacation">О</span>
                                @elseif($status === 'sick_leave')
                                    <span class="attendance__status attendance__status--sick">Б</span>
                                @else
                                    <span class="attendance__status attendance__status--empty">—</span>
                                @endif
                                @if($comment)
                                    <span class="attendance__comment" title="{{ $comment }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="16" x2="12" y2="12"></line>
                                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                        </svg>
                                    </span>
                                @endif
                            @endif
                        </td>
                        @php $d->addDay(); @endphp
                    @endwhile
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Подключаем библиотеку Fuse.js для нечеткого поиска -->
<script src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fixStickyColumns();
    window.addEventListener('resize', fixStickyColumns);
    
    // Инициализация сортировки и фильтрации
    initFuseSearch();
    
    // Проверяем наличие параметра имени в URL и заполняем поле поиска
    const urlParams = new URLSearchParams(window.location.search);
    const nameParam = urlParams.get('name');
    if (nameParam) {
        const filterNameInput = document.getElementById('filterName');
        if (filterNameInput) {
            filterNameInput.value = nameParam;
            // Вызываем функцию фильтрации вручную
            setTimeout(() => {
                const event = new Event('input');
                filterNameInput.dispatchEvent(event);
            }, 300);
        }
    }
});

function fixStickyColumns() {
    // Получаем все колонки первой строки для измерения их ширины
    const firstRowCols = document.querySelector('.attendance__table tr:first-child').children;
    const colWidths = [];
    
    // Измеряем ширину первых трёх колонок
    for (let i = 0; i < 3; i++) {
        colWidths.push(firstRowCols[i].offsetWidth);
    }
    
    // Применяем left ко всем sticky-колонкам на основе измеренной ширины
    const nameColumns = document.querySelectorAll('.attendance__sticky-col--name');
    const posColumns = document.querySelectorAll('.attendance__sticky-col--pos');
    const depColumns = document.querySelectorAll('.attendance__sticky-col--dep');
    
    nameColumns.forEach(col => col.style.left = '0px');
    posColumns.forEach(col => col.style.left = colWidths[0] + 'px');
    depColumns.forEach(col => col.style.left = (colWidths[0] + colWidths[1]) + 'px');
    
    // Обновляем z-index для угловых ячеек (пересечение fixed строк и столбцов)
    const cornerCells = document.querySelectorAll('th.attendance__sticky-col');
    cornerCells.forEach(cell => {
        cell.style.zIndex = '5';
    });
}

function initFuseSearch() {
    const table = document.getElementById('attendanceTable');
    const rows = Array.from(table.querySelectorAll('tbody tr.attendance__data-row'));
    const filterDepartment = document.getElementById('filterDepartment');
    const filterPosition = document.getElementById('filterPosition');
    const filterName = document.getElementById('filterName');
    const resetButton = document.getElementById('resetFilters');
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    
    // Текущие параметры сортировки
    let currentSort = {
        field: 'name',
        order: 'asc'
    };
    
    // Создаем массив данных для Fuse.js
    const searchData = rows.map(row => {
        return {
            element: row,
            name: row.getAttribute('data-full-name'),
            department: row.getAttribute('data-department'),
            position: row.getAttribute('data-position')
        };
    });
    
    // Настройки Fuse.js
    const fuseOptions = {
        keys: ['name'],
        threshold: 0.3, // Чувствительность поиска (0 = точное совпадение, 1 = полностью нечувствительный)
        includeScore: true,
        shouldSort: false // Мы будем сортировать сами
    };
    
    // Инициализация Fuse.js
    const fuse = new Fuse(searchData, fuseOptions);
    
    // Функция фильтрации и сортировки
    function filterAndSortTable() {
        // Фильтрация по имени с помощью Fuse.js
        const nameFilter = filterName.value.trim();
        const departmentFilter = filterDepartment ? filterDepartment.value : null;
        const positionFilter = filterPosition.value;
        
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
        
        // Фильтруем по отделу, если выбран и фильтр доступен
        if (departmentFilter && filterDepartment) {
            filteredData = filteredData.filter(item => item.department === departmentFilter);
        }
        
        // Фильтруем по должности, если выбрана
        if (positionFilter) {
            filteredData = filteredData.filter(item => item.position === positionFilter);
        }
        
        // Отображаем отфильтрованные строки
        const visibleRows = filteredData.map(item => item.element);
        
        // Сортируем видимые строки
        visibleRows.sort((a, b) => {
            let aValue = a.getAttribute(`data-${currentSort.field}`);
            let bValue = b.getAttribute(`data-${currentSort.field}`);
            
            if (currentSort.order === 'asc') {
                return aValue.localeCompare(bValue);
            } else {
                return bValue.localeCompare(aValue);
            }
        });
        
        // Переупорядочиваем строки в таблице
        const tbody = table.querySelector('tbody');
        visibleRows.forEach(row => {
            row.style.display = ''; // Показываем строку
            tbody.appendChild(row);
        });
        
        // Обновляем индикаторы сортировки
        updateSortIcons();
        
        // Обновляем sticky-колонки после всех изменений
        fixStickyColumns();
        
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
    
    // Сортировка при клике на заголовки
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const field = this.getAttribute('data-sort');
            
            // Если кликнули на текущее поле сортировки - меняем порядок
            if (currentSort.field === field) {
                currentSort.order = currentSort.order === 'asc' ? 'desc' : 'asc';
            } else {
                // Иначе устанавливаем новое поле сортировки
                currentSort.field = field;
                currentSort.order = 'asc';
            }
            
            // Обновляем таблицу
            filterAndSortTable();
        });
    });
    
    function updateSortIcons() {
        sortableHeaders.forEach(header => {
            const field = header.getAttribute('data-sort');
            const icon = header.querySelector('.attendance__sort-icon');
            
            if (field === currentSort.field) {
                icon.textContent = currentSort.order === 'asc' ? '↑' : '↓';
                icon.classList.add('attendance__sort-icon--active');
            } else {
                icon.textContent = '↕';
                icon.classList.remove('attendance__sort-icon--active');
            }
        });
    }
    
    // Привязываем обработчики событий для мгновенной фильтрации
    if (filterDepartment) {
    filterDepartment.addEventListener('change', filterAndSortTable);
    }
    filterPosition.addEventListener('change', filterAndSortTable);
    
    // Установка debounce для поиска по имени
    let searchTimeout;
    filterName.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterAndSortTable, 300); // Задержка 300мс
    });
    
    // Сброс фильтров
    resetButton.addEventListener('click', function() {
        if (filterDepartment) {
        filterDepartment.value = '';
        }
        filterPosition.value = '';
        filterName.value = '';
        
        filterAndSortTable();
    });
    
    // Инициализация таблицы при загрузке страницы
    filterAndSortTable();
}
</script>
@endsection

@push('styles')
<style>
.attendance {
    background: #fff;
    border-radius: 10px;
    border: 1.5px solid var(--color-light-gray);
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.attendance__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.attendance__title {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--color-black);
    margin: 0;
}

.attendance__period-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.attendance__period-label {
    font-weight: 600;
    color: var(--color-dark-gray);
}

.attendance__period-input {
    padding: 0.4em 0.8em;
    border-radius: 5px;
    border: 1px solid #ddd;
    font-size: 16px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.attendance__period-input:focus {
    border-color: #F37032;
    outline: none;
    box-shadow: 0 0 0 2px rgba(243, 112, 50, 0.2);
}

.attendance__period-btn {
    background-color: #F37032;
    color: white;
    border: none;
    padding: 0.4em 1em;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.attendance__period-btn:hover {
    background-color: #e06328;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(243, 112, 50, 0.3);
}

.attendance__period-btn svg {
    width: 18px;
    height: 18px;
}

.attendance__filters {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.attendance__filter-group {
    flex: 1;
    min-width: 200px;
    max-width: 300px;
}

.attendance__filter-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
}

.attendance__filter-select,
.attendance__filter-input {
    width: 100%;
    padding: calc(var(--spacing-unit)* 0.8) calc(var(--spacing-unit)* 0.75);
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    appearance: none;
    background-color: #fff;
}

.attendance__filter-select:focus,
.attendance__filter-input:focus {
    border-color: #F37032;
    outline: none;
    box-shadow: 0 0 0 2px rgba(243, 112, 50, 0.2);
}

.attendance__filter-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23555' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 35px;
}

.attendance__filter-reset {
    padding: 0.6rem 1rem;
    background-color: #f0f0f0;
    color: #555;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.attendance__filter-reset:hover {
    background-color: #e0e0e0;
}

.attendance__no-results {
    text-align: center;
    padding: 30px;
    color: #777;
    background-color: #f9f9f9;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #eee;
}

.attendance__table-outer {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #eee;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.attendance__table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.attendance__table th, .attendance__table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.attendance__table th {
    background-color: #f7f7f7;
    font-weight: 600;
    color: #555;
    cursor: pointer;
    white-space: nowrap;
    position: relative;
    padding-right: 25px;
}

.attendance__table th:hover {
    background-color: #f0f0f0;
}

.attendance__table tbody tr {
    transition: background-color 0.2s ease;
}

.attendance__table tbody tr:hover {
    background-color: #f8f8f8;
}

.attendance__table tbody tr:last-child td {
    border-bottom: none;
}

.attendance__sort-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #aaa;
    transition: color 0.3s ease;
}

.attendance__sort-icon--active {
    color: #F37032;
}

.attendance__sticky-col {
    position: sticky;
    background-color: white;
    z-index: 3;
    box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.1);
}

.attendance__sticky-col--name { left: 0; min-width: 160px; max-width: 160px; }
.attendance__sticky-col--pos { left: 160px; min-width: 180px; max-width: 180px; }
.attendance__sticky-col--dep { left: 340px; min-width: 180px; max-width: 180px; }

.attendance__table th.attendance__sticky-col { 
    z-index: 4;
    background-color: #f7f7f7;
}

.attendance__status {
    display: inline-block;
    padding: 0.2em 0.5em;
    border-radius: 5px;
    font-weight: 600;
    font-size: 0.97em;
}
.attendance__status--present { background: #e0f7e9; color: #388E3C; }
.attendance__status--absent { background: #ffeaea; color: var(--color-error); }
.attendance__status--vacation { background: #fff3e0; color: #2f4cf0; }
.attendance__status--sick { background: #e3e6ff;  color: var(--color-primary);}
.attendance__status--empty { color: var(--color-medium-gray); }

.attendance__cell-form {
    display: flex;
    flex-direction: column;
    gap: 0.2em;
    align-items: center;
}

.attendance__status-select {
    width: 100%;
    font-size: 0.97em;
    border-radius: 5px;
    border: 1px solid var(--color-light-gray);
    padding: 0.1em 0.2em;
}

.attendance__comment-input {
    width: 100%;
    font-size: 0.95em;
    border-radius: 5px;
    border: 1px solid var(--color-light-gray);
    padding: 0.1em 0.2em;
    margin-top: 0.1em;
}

.attendance__save-btn {
    margin-top: 0.2em;
    font-size: 0.95em;
    padding: 0.2em 0.7em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.attendance__save-btn svg {
    width: 16px;
    height: 16px;
}

.attendance__comment {
    margin-left: 0.2em;
    color: var(--color-primary);
    cursor: pointer;
}

@media (max-width: 900px) {
    .attendance__table th, .attendance__table td {
        min-width: 36px;
        font-size: 0.92em;
        padding: 0.3em 0.2em;
    }
    .attendance {
        padding: 1rem 0.3rem 1.2rem 0.3rem;
    }
    .attendance__sticky-col--name { left: 0; min-width: 90px; max-width: 90px; }
    .attendance__sticky-col--pos { left: 90px; min-width: 110px; max-width: 110px; }
    .attendance__sticky-col--dep { left: 200px; min-width: 110px; max-width: 110px; }
}
</style>
@endpush 