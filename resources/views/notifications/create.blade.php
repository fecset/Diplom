@extends('layouts.app')
@section('title', 'Создание уведомления')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Создание уведомления</h1>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('notifications.store') }}">
                @csrf
                
                <div class="form-group">
                    <label for="title">Заголовок уведомления <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="message">Текст уведомления <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
                    @error('message')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="type">Тип уведомления <span class="text-danger">*</span></label>
                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Информационное</option>
                        <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Предупреждение</option>
                        <option value="important" {{ old('type') == 'important' ? 'selected' : '' }}>Важное</option>
                    </select>
                    @error('type')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="is_global" name="is_global" value="1" {{ old('is_global') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_global">
                        Глобальное уведомление (для всех пользователей)
                    </label>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Дата начала отображения</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}">
                            @error('start_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">Дата окончания отображения</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}">
                            @error('end_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div id="target-section" class="{{ old('is_global') ? 'd-none' : '' }}">
                    <h4 class="mt-4 mb-3">Целевая аудитория</h4>
                    
                    <div class="card mb-3">
                        <div class="card-header">Роли пользователей</div>
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="role_admin" name="target_roles[]" value="admin" {{ is_array(old('target_roles')) && in_array('admin', old('target_roles')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_admin">Администраторы</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="role_hr" name="target_roles[]" value="hr_specialist" {{ is_array(old('target_roles')) && in_array('hr_specialist', old('target_roles')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_hr">HR-специалисты</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="role_employee" name="target_roles[]" value="employee" {{ is_array(old('target_roles')) && in_array('employee', old('target_roles')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_employee">Сотрудники</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header">Отделы</div>
                        <div class="card-body">
                            @foreach($departments as $department)
                                @if($department)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dept_{{ $loop->index }}" name="target_departments[]" value="{{ $department->id }}" {{ is_array(old('target_departments')) && in_array($department->id, old('target_departments')) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dept_{{ $loop->index }}">{{ $department->name }}</label>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">Создать уведомление</button>
                    <a href="{{ route('notifications.index') }}" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    
    .card-header {
        background-color: #f7f7f7;
        border-bottom: 1px solid #eee;
        padding: 15px 20px;
    }
    
    .card-header h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
    }
    
    .card-body {
        padding: 20px;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .text-danger {
        color: #F44336;
    }
    
    .form-control {
        border-radius: 5px;
        border: 1px solid #ddd;
        padding: 10px 15px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .form-control:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 0.2rem rgba(242, 101, 34, 0.25);
    }
    
    .is-invalid {
        border-color: #F44336;
    }
    
    .invalid-feedback {
        color: #F44336;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    /* Стили для чекбоксов */
    .form-check {
        padding-left: 0;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
    }
    
    .form-check-input {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        border: 2px solid #aaa;
        border-radius: 3px;
        outline: none;
        transition: all 0.2s ease;
        position: relative;
        cursor: pointer;
    }
    
    .form-check-input:checked {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
    }
    
    .form-check-input:checked::after {
        content: '';
        position: absolute;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        top: -1px;
        left: 4px;
        transform: rotate(45deg);
    }
    
    .form-check-input:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 0.2rem rgba(242, 101, 34, 0.25);
    }
    
    .form-check-label {
        font-weight: 400;
        margin-bottom: 0;
        cursor: pointer;
    }
    
    /* Кнопки */
    .btn {
        font-weight: 500;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background-color: var(--color-primary);
        border: none;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #e06328;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        border: none;
        color: white;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
    }
    
    /* Выравнивание секций карт */
    .card-body .card {
        box-shadow: 0 1px 5px rgba(0,0,0,0.03);
    }
    
    .card-body .card-header {
        padding: 10px 15px;
        font-weight: 500;
    }
    
    .card-body .card-body {
        padding: 15px;
        max-height: 200px;
        overflow-y: auto;
    }
    
    @media (max-width: 768px) {
        .row {
            flex-direction: column;
        }
        
        .col-md-6 {
            width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isGlobalCheckbox = document.getElementById('is_global');
        const targetSection = document.getElementById('target-section');
        
        isGlobalCheckbox.addEventListener('change', function() {
            if (this.checked) {
                targetSection.classList.add('d-none');
            } else {
                targetSection.classList.remove('d-none');
            }
        });
        
        // Добавляем стили для отображения чекбоксов в соответствии с их состоянием
        const checkboxes = document.querySelectorAll('.form-check-input');
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                checkbox.classList.add('checked');
            }
            
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    this.classList.add('checked');
                } else {
                    this.classList.remove('checked');
                }
            });
        });
    });
</script>
@endsection 