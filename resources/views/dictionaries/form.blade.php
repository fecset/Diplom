@extends('layouts.app')
@section('title', $title)
@php $use_wrapper = true; @endphp
@section('content')
<div class="ui-page">
    @include('partials.status-messages')
    <a class="ui-back-link" href="{{ route('admin.'.$kind.'.index') }}"><x-icon name="arrow-left"/>{{ $title }}</a>
    <header class="ui-page-header"><div><p class="ui-eyebrow">Справочники</p><h1>{{ $item->exists ? 'Изменить запись' : 'Новая запись' }}</h1><p class="ui-description">{{ $kind === 'departments' ? 'Укажите название отдела компании.' : 'Укажите название должности.' }}</p></div></header>
    <form class="ui-card ui-form-card" method="POST" action="{{ $item->exists ? route('admin.'.$kind.'.update', $item) : route('admin.'.$kind.'.store') }}">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="ui-card-header"><span class="ui-section-icon"><x-icon :name="$kind === 'departments' ? 'building' : 'briefcase'"/></span><h2>{{ $kind === 'departments' ? 'Данные отдела' : 'Данные должности' }}</h2></div>
        <div class="ui-card-body"><div class="ui-field"><label for="dictionaryName">Название <span class="ui-required" aria-hidden="true">*</span></label><input id="dictionaryName" type="text" name="name" value="{{ old('name', $item->name) }}" maxlength="255" required aria-describedby="dictionaryHelp @error('name') dictionaryError @enderror" @error('name') aria-invalid="true" @enderror><p class="ui-field-help" id="dictionaryHelp">Название должно быть уникальным. Максимум 255 символов.</p>@error('name')<p class="ui-field-error" id="dictionaryError">{{ $message }}</p>@enderror</div></div>
        <div class="ui-form-actions ui-form-actions--bordered"><button class="ui-button" type="submit"><x-icon name="check"/>Сохранить</button><a class="ui-button ui-button--secondary" href="{{ route('admin.'.$kind.'.index') }}">Отмена</a></div>
    </form>
</div>
@endsection
