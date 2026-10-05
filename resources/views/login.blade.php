@extends('layouts.app')
@section('title', 'Вход в систему')

@section('content')
<div class="auth">
    <div class="auth__card">
        @if (session('success'))
            <div class="auth__alert alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="auth__alert alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="auth__alert alert alert-danger">
                <strong>Ошибка!</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <h1 class="auth__title">Вход в систему</h1>
        <form method="POST" action="{{ route('login') }}" class="auth__form" autocomplete="off">
            @csrf
            <div class="auth__field">
                <label for="username">Логин сотрудника</label>
                <div class="auth__input-wrap">
                    <input id="username" type="text" name="username" class="auth__input" value="{{ old('username') }}" required autofocus autocomplete="username">
                </div>
            </div>
            <div class="auth__field">
                <label for="password">Пароль</label>
                <div class="auth__input-wrap">
                    <input id="password" type="password" name="password" class="auth__input" required autocomplete="current-password">
                </div>
            </div>
            <div class="auth__checkbox-row">
                <input type="checkbox" name="remember" id="remember" class="auth__checkbox" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" class="auth__checkbox-label">Запомнить меня</label>
            </div>
            <button type="submit" class="auth__submit">Войти</button>
        </form>
    </div>
</div>
@endsection
