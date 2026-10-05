@extends('layouts.app')
@section('title','Табель учёта рабочего времени')
@php $special_container=true; $labels=['present'=>'Я','absent'=>'Н','vacation'=>'О','sick_leave'=>'Б']; @endphp
@section('content')
<div class="attendance"><h1>Табель учёта рабочего времени</h1>
<form method="GET" class="review-filters">
<label for="attendanceMonth">Месяц</label><input type="month" id="attendanceMonth" name="date" value="{{ $start->format('Y-m') }}" required>
<label for="attendanceName">Имя сотрудника</label><input id="attendanceName" name="name" value="{{ request('name') }}">
<label for="attendanceDepartment">Отдел</label><select id="attendanceDepartment" name="department"><option value="">Все доступные</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(request('department')==$department->id)>{{ $department->name }}</option>@endforeach</select>
<label for="attendancePosition">Должность</label><select id="attendancePosition" name="position"><option value="">Все</option>@foreach($positions as $position)<option value="{{ $position->id }}" @selected(request('position')==$position->id)>{{ $position->name }}</option>@endforeach</select>
<button class="btn" type="submit">Показать</button></form>
<p>Я — явка / командировка; Н — неявка; О — отпуск; Б — больничный; — — нет отметки.</p>
<div class="review-scroll"><table class="review-table attendance-grid"><thead><tr><th>Сотрудник</th><th>Должность</th><th>Отдел</th>
@for($d=$start;$d->lte($end);$d=$d->addDay())<th>{{ $d->format('d') }}</th>@endfor
</tr></thead><tbody>
@forelse($users as $user)<tr><th scope="row">{{ $user->name }}</th><td>{{ $user->position?->name??'—' }}</td><td>{{ $user->department?->name??'—' }}</td>
@for($d=$start;$d->lte($end);$d=$d->addDay())
@php $att=$attendances[$user->id][$d->toDateString()][0]??null; @endphp
<td class="attendance-status-{{ $att?->status??'unknown' }}">
@if(auth()->user()->isAdmin()||auth()->user()->isHrSpecialist())
<button type="button" class="attendance-edit" aria-label="{{ $user->name }}, {{ $d->format('d.m.Y') }}: {{ $labels[$att?->status]??'нет отметки' }}" data-user="{{ $user->id }}" data-name="{{ $user->name }}" data-date="{{ $d->toDateString() }}" data-status="{{ $att?->status }}" data-comment="{{ $att?->comment }}">{{ $labels[$att?->status]??'—' }}</button>
@else<span title="{{ $att?->comment }}">{{ $labels[$att?->status]??'—' }}</span>@endif
</td>@endfor</tr>@empty<tr><td colspan="34">Сотрудники не найдены.</td></tr>@endforelse
</tbody></table></div>{{ $users->links('pagination.custom') }}</div>
@if(auth()->user()->isAdmin()||auth()->user()->isHrSpecialist())
<dialog id="attendanceDialog" class="review-dialog" aria-labelledby="attendanceDialogTitle"><h2 id="attendanceDialogTitle">Отметка в табеле</h2>
<form method="POST" action="{{ route('hr.attendance.store') }}" class="review-form">@csrf
<input type="hidden" name="user_id"><input type="hidden" name="date">
<label for="attendanceStatus">Статус</label><select id="attendanceStatus" name="status" required><option value="">Выберите статус</option><option value="present">Явка</option><option value="absent">Неявка</option><option value="vacation">Отпуск</option><option value="sick_leave">Больничный</option></select>
<label for="attendanceComment">Комментарий</label><input id="attendanceComment" name="comment" maxlength="255">
<button class="btn" type="submit">Сохранить</button><button type="button" data-close-dialog>Отмена</button>
</form></dialog>@endif
@endsection
