@extends('layouts.app')
@section('title', 'Новая заявка')

@section('content')
<div class="request-form">
    <div class="request-form__card">
        <h2 class="request-form__title">Новая заявка</h2>
        
        <form method="POST" action="{{ route('leave_requests.store') }}" class="request-form__form" enctype="multipart/form-data">
            @csrf
            
            <div class="request-form__field">
                <label for="type">Тип заявки:</label>
                <select name="type" id="type" class="request-form__select" required>
                    <option value="vacation" {{ $type === 'vacation' ? 'selected' : '' }}>Отпуск</option>
                    <option value="sick_leave" {{ $type === 'sick_leave' ? 'selected' : '' }}>Больничный</option>
                    <option value="business_trip" {{ $type === 'business_trip' ? 'selected' : '' }}>Командировка</option>
                </select>
            </div>

            <div class="request-form__field">
                <label for="date_start">Дата начала:</label>
                <input type="date" name="date_start" id="date_start" required>
            </div>

            <div class="request-form__field">
                <label for="date_end">Дата окончания:</label>
                <input type="date" name="date_end" id="date_end" required>
            </div>

            <div class="request-form__field business-trip-fields" style="display: none;">
                <label for="destination">Место командировки:</label>
                <input type="text" name="destination" id="destination" placeholder="Укажите место командировки">
            </div>

            <div class="request-form__field business-trip-fields" style="display: none;">
                <label for="purpose">Цель командировки:</label>
                <textarea name="purpose" id="purpose" class="request-form__textarea" placeholder="Укажите цель командировки"></textarea>
            </div>

            <div class="request-form__field">
                <label for="reason">Причина:</label>
                <textarea name="reason" id="reason" class="request-form__textarea" placeholder="Укажите причину (необязательно)"></textarea>
            </div>

            <div class="request-form__field">
                <label for="document">Документ:</label>
                <input type="file" name="document" id="document" accept=".jpeg,.jpg,.png,.pdf">
                <small class="request-form__help">Максимальный размер файла: 5MB. Разрешенные форматы: JPEG, PNG, PDF</small>
            </div>

            <div class="request-form__actions">
                <a href="{{ route('leave_requests.index') }}" class="btn btn--gray">Отмена</a>
                <button type="submit" class="request-form__btn">Отправить заявку</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const businessTripFields = document.querySelectorAll('.business-trip-fields');
    
    function toggleBusinessTripFields() {
        const isBusinessTrip = typeSelect.value === 'business_trip';
        businessTripFields.forEach(field => {
            field.style.display = isBusinessTrip ? 'block' : 'none';
            const input = field.querySelector('input, textarea');
            if (input) {
                input.required = isBusinessTrip;
            }
        });
    }
    
    typeSelect.addEventListener('change', toggleBusinessTripFields);
    toggleBusinessTripFields(); // Вызываем при загрузке страницы
});
</script>

<style>
.request-form__select {
    width: 100%;
    padding: 0.7rem 1rem;
    font-size: 1.02rem;
    color: var(--color-black);
    background: var(--color-background);
    border: 1.2px solid var(--color-light-gray);
    border-radius: 7px;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
    box-shadow: none;
    font-weight: 500;
    letter-spacing: 0.01em;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23757575' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 12px;
    padding-right: 2.5rem;
    box-sizing: border-box;
}

.request-form__select:focus {
    border-color: var(--color-primary);
    background: #fff7f3;
    box-shadow: 0 0 0 2px rgba(243, 112, 50, 0.10);
}

.request-form__field input[type='date'],
.request-form__field input[type='text'] {
    width: 100%;
    font-size: 1.02rem;
    color: var(--color-black);
    background: var(--color-background);
    border: 1.2px solid var(--color-light-gray);
    border-radius: 7px;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
    box-shadow: none;
    font-weight: 500;
    letter-spacing: 0.01em;
    box-sizing: border-box;
}

.request-form__textarea {
    width: 100%;
    padding: 0.7rem 2.5rem 0.7rem 1rem;
    font-size: 1.02rem;
    color: var(--color-black);
    background: var(--color-background);
    border: 1.2px solid var(--color-light-gray);
    border-radius: 7px;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
    box-shadow: none;
    font-weight: 500;
    letter-spacing: 0.01em;
    min-height: 100px;
    resize: vertical;
    box-sizing: border-box;
}

.request-form__textarea:focus {
    border-color: var(--color-primary);
    background: #fff7f3;
    box-shadow: 0 0 0 2px rgba(243, 112, 50, 0.10);
}

.request-form__help {
    display: block;
    margin-top: 0.3rem;
    font-size: 0.9rem;
    color: var(--color-medium-gray);
}
</style>
@endsection