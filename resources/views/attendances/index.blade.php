@extends('layouts.app')
@section('title', 'Табель учёта рабочего времени')
@php
    $labels = ['present'=>'Я', 'absent'=>'Н', 'vacation'=>'О', 'sick_leave'=>'Б'];
    $statusNames = ['present'=>'Явка / командировка', 'absent'=>'Неявка', 'vacation'=>'Отпуск', 'sick_leave'=>'Больничный'];
    $weekdays = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
    $canEdit = auth()->user()->isAdmin() || auth()->user()->isHrSpecialist();
@endphp
@section('content')
<div class="ui-page">
    <header class="ui-page-header"><div><h1>Табель учёта рабочего времени</h1><p class="ui-description">{{ $canEdit ? 'Нажмите на день сотрудника, чтобы изменить отметку.' : 'Отметки рабочего времени сотрудников вашего отдела.' }} Для просмотра всех дней прокрутите таблицу вправо.</p></div></header>
    <form method="GET" class="ui-filter-bar">
        <div><label for="attendanceMonth">Месяц</label><input type="month" id="attendanceMonth" name="date" value="{{ $start->format('Y-m') }}" required></div>
        <div><label for="attendanceName">Имя сотрудника</label><input type="text" id="attendanceName" name="name" placeholder="Поиск по имени" value="{{ request('name') }}"></div>
        <div><label for="attendanceDepartment">Отдел</label><select id="attendanceDepartment" name="department"><option value="">Все доступные</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(request('department') == $department->id)>{{ $department->name }}</option>@endforeach</select></div>
        <div><label for="attendancePosition">Должность</label><select id="attendancePosition" name="position"><option value="">Все должности</option>@foreach($positions as $position)<option value="{{ $position->id }}" @selected(request('position') == $position->id)>{{ $position->name }}</option>@endforeach</select></div>
        <div class="ui-filter-actions"><button class="ui-button" type="submit"><x-icon name="search"/>Показать</button><a class="ui-button ui-button--secondary" href="{{ route('hr.attendance.index', ['date' => $start->format('Y-m')]) }}">Сбросить</a></div>
    </form>
    <section class="ui-card" aria-label="Отметки за {{ $start->format('m.Y') }}">
        <div class="ui-list-heading"><h2>Отметки за {{ $start->format('m.Y') }}</h2><span class="ui-description">Сотрудники: {{ $users->total() }}</span></div>
        <ul class="ui-attendance-legend">
            @foreach($labels as $status => $symbol)<li><span class="ui-attendance-symbol attendance-status-{{ $status }}">{{ $symbol }}</span>{{ $statusNames[$status] }}</li>@endforeach
            <li><span class="ui-attendance-symbol">—</span>Нет отметки</li>
        </ul>
        <div class="ui-attendance-scroll" tabindex="0" role="region" aria-label="Табель: прокручиваемая таблица">
            <table class="ui-attendance-table"><thead><tr><th scope="col" class="ui-attendance-name">Сотрудник</th>
                @for($d = $start; $d->lte($end); $d = $d->addDay())<th scope="col" @class(['ui-attendance-weekend' => $d->isWeekend()])>{{ $d->format('d') }}<small>{{ $weekdays[$d->dayOfWeek] }}</small></th>@endfor
            </tr></thead><tbody>
                @forelse($users as $user)<tr><th scope="row" class="ui-attendance-name">{{ $user->name }}<span>{{ $user->position?->name ?? 'Без должности' }} · {{ $user->department?->name ?? 'Без отдела' }}</span></th>
                    @for($d = $start; $d->lte($end); $d = $d->addDay())
                        @php $att = $attendances[$user->id][$d->toDateString()][0] ?? null; @endphp
                        <td class="attendance-status-{{ $att?->status ?? 'unknown' }}">
                            @if($canEdit)
                                <button type="button" class="attendance-edit" title="{{ $statusNames[$att?->status] ?? 'Нет отметки' }}{{ $att?->comment ? ': '.$att->comment : '' }}" aria-label="{{ $user->name }}, {{ $d->format('d.m.Y') }}: {{ $statusNames[$att?->status] ?? 'нет отметки' }}" data-user="{{ $user->id }}" data-name="{{ $user->name }}" data-date="{{ $d->toDateString() }}" data-status="{{ $att?->status }}" data-comment="{{ $att?->comment }}">{{ $labels[$att?->status] ?? '—' }}</button>
                            @else<span title="{{ $statusNames[$att?->status] ?? 'Нет отметки' }}{{ $att?->comment ? ': '.$att->comment : '' }}">{{ $labels[$att?->status] ?? '—' }}</span>@endif
                        </td>
                    @endfor
                </tr>@empty<tr><td colspan="{{ $end->day + 1 }}"><div class="ui-empty"><h2>Сотрудники не найдены</h2><p>Измените фильтры или сбросьте параметры поиска.</p></div></td></tr>@endforelse
            </tbody></table>
        </div>
        <div class="ui-attendance-summary">Показаны сотрудники {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} из {{ $users->total() }}.</div>
        {{ $users->links('pagination.custom') }}
    </section>
</div>
@if($canEdit)
<dialog id="attendanceDialog" class="review-dialog" aria-labelledby="attendanceDialogTitle" aria-describedby="attendanceDialogContext">
    <div class="ui-dialog-header"><div><h2 id="attendanceDialogTitle">Отметка в табеле</h2><p id="attendanceDialogContext"></p></div><button class="ui-icon-button" type="button" data-close-dialog aria-label="Закрыть"><x-icon name="close"/></button></div>
    <form method="POST" action="{{ route('hr.attendance.store') }}">@csrf
        <input type="hidden" name="user_id"><input type="hidden" name="date">
        <div class="ui-card-body">
            <div class="ui-field"><label for="attendanceStatus">Статус <span class="ui-required" aria-hidden="true">*</span></label><select id="attendanceStatus" name="status" required><option value="">Выберите статус</option><option value="present">Явка / командировка</option><option value="absent">Неявка</option><option value="vacation">Отпуск</option><option value="sick_leave">Больничный</option></select></div>
            <div class="ui-field"><label for="attendanceComment">Комментарий</label><input type="text" id="attendanceComment" name="comment" maxlength="255" aria-describedby="attendanceCommentHelp"><p class="ui-field-help" id="attendanceCommentHelp">Необязательно. Максимум 255 символов.</p></div>
        </div>
        <div class="ui-form-actions ui-form-actions--bordered"><button class="ui-button" type="submit"><x-icon name="check"/>Сохранить отметку</button><button class="ui-button ui-button--secondary" type="button" data-close-dialog>Отмена</button></div>
    </form>
</dialog>
@endif
@endsection
