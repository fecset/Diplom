@extends('layouts.app')
@section('title', 'Мой профиль')
@section('content')
<div class="ui-page">
    <header class="ui-page-header">
        <div><h1>Мой профиль</h1><p class="ui-description">Контактные данные и безопасность вашей учётной записи.</p></div>
    </header>
    <form method="POST" action="{{ route('profile.update') }}" class="ui-profile-form">
        @csrf @method('PATCH')
        <div class="ui-profile-grid">
            <section class="ui-card" aria-labelledby="contactHeading">
                <div class="ui-identity"><span class="ui-avatar"><x-icon name="user"/></span><div><h2>{{ $user->name }}</h2><p>Логин: {{ $user->username }}</p></div></div>
                <div class="ui-card-body">
                    <h2 id="contactHeading" class="ui-section-title">Контактные данные</h2>
                    <div class="ui-field"><label for="profileEmail">Email</label><input id="profileEmail" type="email" name="email" autocomplete="email" value="{{ old('email', $user->email) }}" @error('email') aria-invalid="true" aria-describedby="emailError" @enderror>@error('email')<p class="ui-field-error" id="emailError">{{ $message }}</p>@enderror</div>
                    <div class="ui-field"><label for="profilePhone">Телефон</label><input id="profilePhone" type="tel" name="phone_number" maxlength="20" autocomplete="tel" value="{{ old('phone_number', $user->phone_number) }}" @error('phone_number') aria-invalid="true" aria-describedby="phoneError" @enderror>@error('phone_number')<p class="ui-field-error" id="phoneError">{{ $message }}</p>@enderror</div>
                </div>
            </section>
            <section class="ui-card" aria-labelledby="passwordHeading">
                <div class="ui-card-header"><span class="ui-section-icon"><x-icon name="lock"/></span><div><h2 id="passwordHeading">Смена пароля</h2><p>Оставьте поля пустыми, если пароль менять не нужно.</p></div></div>
                <div class="ui-card-body">
                    <div class="ui-field"><label for="currentPassword">Текущий пароль</label><input id="currentPassword" type="password" name="current_password" autocomplete="current-password" @error('current_password') aria-invalid="true" aria-describedby="currentPasswordError" @enderror>@error('current_password')<p class="ui-field-error" id="currentPasswordError">{{ $message }}</p>@enderror</div>
                    <div class="ui-field"><label for="newPassword">Новый пароль</label><input id="newPassword" type="password" name="password" minlength="8" autocomplete="new-password" aria-describedby="passwordHelp @error('password') passwordError @enderror" @error('password') aria-invalid="true" @enderror><p class="ui-field-help" id="passwordHelp">Минимум 8 символов.</p>@error('password')<p class="ui-field-error" id="passwordError">{{ $message }}</p>@enderror</div>
                    <div class="ui-field"><label for="confirmPassword">Повторите новый пароль</label><input id="confirmPassword" type="password" name="password_confirmation" autocomplete="new-password"></div>
                </div>
            </section>
        </div>
        <div class="ui-form-actions"><button class="ui-button" type="submit"><x-icon name="check"/>Сохранить изменения</button></div>
    </form>
</div>
@endsection
