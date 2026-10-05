@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="ui-page">
    <header class="ui-page-header">
        <div><h1>{{ $title }}</h1><p class="ui-description">{{ $kind === 'departments' ? 'Структура компании для кадрового учёта и фильтрации сотрудников.' : 'Должности, доступные в карточках сотрудников.' }}</p></div>
        <a class="ui-button" href="{{ route('admin.'.$kind.'.create') }}"><x-icon name="plus"/>Добавить запись</a>
    </header>
    <section class="ui-card" aria-label="{{ $title }}">
        <div class="ui-list-heading"><h2>Все записи <span class="ui-count">{{ $items->total() }}</span></h2><span class="ui-description">{{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} из {{ $items->total() }}</span></div>
        <div class="ui-table-scroll"><table class="ui-table"><thead><tr><th scope="col">Название</th><th scope="col" class="ui-actions-heading">Действия</th></tr></thead><tbody>
            @forelse($items as $item)
                <tr><td><div class="ui-record-name"><span class="ui-record-icon"><x-icon :name="$kind === 'departments' ? 'building' : 'briefcase'"/></span>{{ $item->name }}</div></td><td><div class="ui-row-actions">
                    <a class="ui-button ui-button--secondary ui-button--compact" href="{{ route('admin.'.$kind.'.edit', $item) }}" aria-label="Изменить: {{ $item->name }}"><x-icon name="edit"/><span>Изменить</span></a>
                    <form method="POST" action="{{ route('admin.'.$kind.'.destroy', $item) }}" onsubmit="return confirm('Удалить запись?')">@csrf @method('DELETE')<button class="ui-button ui-button--danger ui-button--compact" type="submit" aria-label="Удалить: {{ $item->name }}"><x-icon name="trash"/><span>Удалить</span></button></form>
                </div></td></tr>
            @empty
                <tr><td colspan="2"><div class="ui-empty"><span class="ui-avatar"><x-icon :name="$kind === 'departments' ? 'building' : 'briefcase'"/></span><h2>Пока нет записей</h2><p>Добавьте первую запись, чтобы использовать её в карточках сотрудников.</p><a class="ui-button" href="{{ route('admin.'.$kind.'.create') }}"><x-icon name="plus"/>Добавить запись</a></div></td></tr>
            @endforelse
        </tbody></table></div>
        {{ $items->links('pagination.custom') }}
    </section>
    <p class="ui-footnote">Записи, связанные с сотрудниками, защищены от удаления.</p>
</div>
@endsection
