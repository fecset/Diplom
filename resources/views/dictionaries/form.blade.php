@extends('layouts.app')
@section('title',$title)
@section('content')
<h1>{{ $title }}: {{ $item->exists?'изменение':'новая запись' }}</h1>
<form class="review-form" method="POST" action="{{ $item->exists?route('admin.'.$kind.'.update',$item):route('admin.'.$kind.'.store') }}">
@csrf @if($item->exists) @method('PUT') @endif
<label for="dictionaryName">Название</label><input id="dictionaryName" name="name" value="{{ old('name',$item->name) }}" maxlength="255" required>
<button class="btn" type="submit">Сохранить</button><a href="{{ route('admin.'.$kind.'.index') }}">Отмена</a>
</form>@endsection
