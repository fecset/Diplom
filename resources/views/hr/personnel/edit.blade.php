@extends('layouts.app')
@section('title', 'Редактирование данных сотрудника')

@section('content')

    <div class="personnel-form">
        <div class="personnel-form__header">
            <h2 class="personnel-form__title">Редактирование данных сотрудника</h2>
            <a href="{{ route('hr.personnel.index') }}" class="btn btn--gray personnel-form__back">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Вернуться к списку</span>
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
        
        <form action="{{ route('hr.personnel.update', $user) }}" method="POST" class="personnel-form__form">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="name" class="form-label">ФИО сотрудника*:</label>
                <input type="text" name="name" id="name" class="form-input" value="{{ old('name', $user->name) }}" required>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group--half">
                    <label for="department_id" class="form-label">Отдел*:</label>
                    <select name="department_id" id="department_id" class="form-select" required>
                        <option value="">Выберите отдел</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id', $user->department_id) == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group form-group--half">
                    <label for="position_id" class="form-label">Должность*:</label>
                    <select name="position_id" id="position_id" class="form-select" required>
                        <option value="">Выберите должность</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" @selected(old('position_id', $user->position_id) == $position->id)>{{ $position->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group--half">
                    <label for="username" class="form-label">Логин*:</label>
                    <input type="text" name="username" id="username" class="form-input" value="{{ old('username', $user->username) }}" required>
                </div>
                
                <div class="form-group form-group--half">
                    <label for="hired_at" class="form-label">Дата приема на работу:</label>
                    <input type="date" name="hired_at" id="hired_at" class="form-input form-input--date" 
                           value="{{ old('hired_at', $user->hired_at ? \Carbon\Carbon::parse($user->hired_at)->format('Y-m-d') : '') }}">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group--half">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" id="email" class="form-input" value="{{ old('email', $user->email) }}">
                </div>
                
                <div class="form-group form-group--half">
                    <label for="phone_number" class="form-label">Телефон:</label>
                    <input type="tel" name="phone_number" id="phone_number" class="form-input" value="{{ old('phone_number', $user->phone_number) }}" placeholder="+7 (___) ___-__-__">
                </div>
            </div>
            
            <div class="form-group">
                <label for="role" class="form-label">Роль в системе*:</label>
                <select name="role" id="role" class="form-select" required>
                    <option value="employee" {{ old('role', $user->role) == 'employee' ? 'selected' : '' }}>Сотрудник</option>
                    <option value="hr_specialist" {{ old('role', $user->role) == 'hr_specialist' ? 'selected' : '' }}>HR-специалист</option>
                    @if(Auth::user()->isAdmin())
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Администратор</option>
                    @endif
                </select>
            </div>
            
            <div class="form-divider"></div>
            
            <div class="form-group">
                <h3 class="password-section__title">Изменение пароля</h3>
                <p class="password-section__note">Оставьте поля пустыми, если не хотите менять пароль</p>
            </div>
            
            <div class="form-row">
                <div class="form-group form-group--half">
                    <label for="password" class="form-label">Новый пароль:</label>
                    <input type="password" name="password" id="password" class="form-input">
                </div>
                
                <div class="form-group form-group--half">
                    <label for="password_confirmation" class="form-label">Подтверждение пароля:</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-input">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn personnel-form__submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Сохранить</span>
                </button>
                <a href="{{ route('hr.personnel.index') }}" class="btn btn--gray">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    <span>Отмена</span>
                </a>
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

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 25px;
    border-radius: 5px;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
}

.btn svg {
    width: 18px;
    height: 18px;
}

.btn--gray {
    background-color: #f5f5f5;
    color: #333;
    border: 1px solid #ddd;
}

.btn--gray:hover {
    background-color: #e0e0e0;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.personnel-form__submit {
    background-color: #F37032;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
}

.personnel-form__submit:hover {
    background-color: #e06328;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(243, 112, 50, 0.3);
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

.password-section__title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0 0 5px 0;
}

.password-section__note {
    color: #777;
    font-size: 14px;
    margin: 0;
}

.form-input--date {
    box-sizing: border-box;
}
</style>
@endsection 