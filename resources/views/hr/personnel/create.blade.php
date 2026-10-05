@extends('layouts.app')
@section('title', 'Добавление сотрудника')

@section('content')

    <div class="personnel-form">
        <div class="personnel-form__header">
            <h2 class="personnel-form__title">Добавление нового сотрудника</h2>
            <a href="{{ route('hr.personnel.index') }}" class="btn btn--gray personnel-form__back">
                Вернуться к списку
            </a>
        </div>
        
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="alert__list">
                    @foreach ($errors->all() as $error)
                        <li class="alert__item">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('hr.personnel.store') }}" method="POST" class="personnel-form__form">
            @csrf
            
            <div class="form-group">
                <label for="name" class="form-label">ФИО сотрудника*:</label>
                <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}" required>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group--half">
                    <label for="department_id" class="form-label">Отдел*:</label>
                    <select name="department_id" id="department_id" class="form-select" required>
                        <option value="">Выберите отдел</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group form-group--half">
                    <label for="position_id" class="form-label">Должность*:</label>
                    <select name="position_id" id="position_id" class="form-select" required>
                        <option value="">Выберите должность</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>{{ $position->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group--half">
                    <label for="username" class="form-label">Логин*:</label>
                    <input type="text" name="username" id="username" class="form-input" value="{{ old('username') }}" required>
                </div>
                
                <div class="form-group form-group--half">
                    <label for="hired_at" class="form-label">Дата приема на работу:</label>
                    <input type="date" name="hired_at" id="hired_at" class="form-input form-input--date" value="{{ old('hired_at') }}">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group--half">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" id="email" class="form-input" value="{{ old('email') }}">
                </div>
                
                <div class="form-group form-group--half">
                    <label for="phone_number" class="form-label">Телефон:</label>
                    <input type="tel" name="phone_number" id="phone_number" class="form-input" value="{{ old('phone_number') }}" placeholder="+7 (___) ___-__-__">
                </div>
            </div>
            
            <div class="form-group">
                <label for="role" class="form-label">Роль в системе*:</label>
                <select name="role" id="role" class="form-select" required>
                    <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Сотрудник</option>
                    <option value="hr_specialist" {{ old('role') == 'hr_specialist' ? 'selected' : '' }}>HR-специалист</option>
                    @if(Auth::user()->isAdmin())
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Администратор</option>
                    @endif
                </select>
            </div>
            
            <div class="form-divider"></div>
            
            <div class="form-row">
                <div class="form-group form-group--half">
                    <label for="password" class="form-label">Пароль*:</label>
                    <input type="password" name="password" id="password" class="form-input" required>
                </div>
                
                <div class="form-group form-group--half">
                    <label for="password_confirmation" class="form-label">Подтверждение пароля*:</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" required>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn personnel-form__submit">Добавить сотрудника</button>
                <a href="{{ route('hr.personnel.index') }}" class="btn btn--gray">Отмена</a>
            </div>
        </form>
    </div>


<style>
.personnel-form {
    background: #fff;
    border-radius: 10px;
    border: 1.5px solid var(--color-light-gray);
    padding: 30px;
    margin-bottom: 30px;
}

.personnel-form__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.personnel-form__title {
    font-size: 24px;
    font-weight: 600;
    color: var(--color-black);
    margin: 0;
}

.personnel-form__back {
    text-decoration: none;
}

.form-group {
    margin-bottom: 20px;
}

.form-group--half {
    width: 48%;
}

.form-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
}

.form-input,
.form-select {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
    transition: border-color 0.3s ease;
    height: 45px;
}

.form-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding-right: 30px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23555' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
}

.form-input:focus,
.form-select:focus {
    border-color: #F37032;
    outline: none;
    box-shadow: 0 0 0 3px rgba(243, 112, 50, 0.1);
}

.form-divider {
    height: 1px;
    background-color: #eee;
    margin: 35px 0;
}

.form-actions {
    display: flex;
    justify-content: flex-start;
    gap: 15px;
    margin-top: 35px;
}

.personnel-form__submit {
    background-color: #F37032;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.personnel-form__submit:hover {
    background-color: #e06328;
}

.btn {
    display: inline-block;
    padding: 12px 25px;
    border-radius: 5px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
    border: none;
}

.btn--gray {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
}

.btn--gray:hover {
    background-color: #e0e0e0;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 25px;
}

.alert-danger {
    background-color: #ffebee;
    border-left: 4px solid #f44336;
    color: #d32f2f;
}

.alert__list {
    margin: 0;
    padding-left: 20px;
}

.alert__item {
    margin-bottom: 5px;
}

.alert__item:last-child {
    margin-bottom: 0;
}

.form-input--date {
    box-sizing: border-box;
}
</style>
@endsection 