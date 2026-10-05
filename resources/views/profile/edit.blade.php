@extends('layouts.app')
@section('title','Профиль')
@section('content')
<h1>Мой профиль</h1><p>{{ $user->name }} · {{ $user->username }}</p>
<form class="review-form" method="POST" action="{{ route('profile.update') }}">@csrf @method('PATCH')
<label for="profileEmail">Email</label><input id="profileEmail" type="email" name="email" value="{{ old('email',$user->email) }}">
<label for="profilePhone">Телефон</label><input id="profilePhone" name="phone_number" maxlength="20" value="{{ old('phone_number',$user->phone_number) }}">
<label for="currentPassword">Текущий пароль (для смены пароля)</label><input id="currentPassword" type="password" name="current_password" autocomplete="current-password">
<label for="newPassword">Новый пароль, минимум 8 символов</label><input id="newPassword" type="password" name="password" minlength="8" autocomplete="new-password">
<label for="confirmPassword">Повторите новый пароль</label><input id="confirmPassword" type="password" name="password_confirmation" autocomplete="new-password">
<button class="btn" type="submit">Сохранить</button></form>
@endsection
