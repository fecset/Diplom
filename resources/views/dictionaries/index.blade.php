@extends('layouts.app')
@section('title',$title)
@section('content')
<h1>{{ $title }}</h1><a class="btn" href="{{ route('admin.'.$kind.'.create') }}">Добавить запись</a>
<table class="review-table"><thead><tr><th>Название</th><th>Действия</th></tr></thead><tbody>
@forelse($items as $item)<tr><td>{{ $item->name }}</td><td><a href="{{ route('admin.'.$kind.'.edit',$item) }}">Изменить</a>
<form class="d-inline" method="POST" action="{{ route('admin.'.$kind.'.destroy',$item) }}" onsubmit="return confirm('Удалить запись?')">@csrf @method('DELETE')<button type="submit">Удалить</button></form></td></tr>
@empty<tr><td colspan="2">Записей нет.</td></tr>@endforelse
</tbody></table>{{ $items->links('pagination.custom') }}
@endsection
