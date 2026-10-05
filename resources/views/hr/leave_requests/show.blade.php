@extends('layouts.app')
@section('title', 'Детали заявки')

@section('content')
<div class="request-details">
    <div class="request-details__header">
        <h2 class="request-details__title">Детали заявки #{{ $leaveRequest->id }}</h2>
        <a href="{{ route('hr.leave_requests.index') }}" class="request-details__back-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Вернуться к списку
        </a>
    </div>
    
    <div class="request-details__content">
        <div class="request-details__info-section">
            <div class="request-details__employee-card">
                <h3 class="request-details__section-title">Информация о сотруднике</h3>
                <div class="request-details__employee-info">
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">ФИО:</div>
                        <div class="request-details__info-value">
                            <a href="{{ route('hr.personnel.show', $leaveRequest->user) }}" class="request-details__link">
                                {{ $leaveRequest->user->name }}
                            </a>
                        </div>
                    </div>
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">Должность:</div>
                        <div class="request-details__info-value">{{ $leaveRequest->user->position?->name ?? '—' }}</div>
                    </div>
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">Отдел:</div>
                        <div class="request-details__info-value">{{ $leaveRequest->user->department?->name ?? '—' }}</div>
                    </div>
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">Email:</div>
                        <div class="request-details__info-value">{{ $leaveRequest->user->email }}</div>
                    </div>
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">Телефон:</div>
                        <div class="request-details__info-value">{{ $leaveRequest->user->phone_number }}</div>
                    </div>
                </div>
            </div>
            
            <div class="request-details__request-card">
                <h3 class="request-details__section-title">Детали заявки</h3>
                <div class="request-details__request-info">
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">Тип:</div>
                        <div class="request-details__info-value">
                            @if($leaveRequest->type === 'vacation')
                                <span class="request-details__badge request-details__badge--vacation">Отпуск</span>
                            @elseif($leaveRequest->type === 'business_trip')
                                <span class="request-details__badge request-details__badge--business_trip">Командировка</span>
                            @else
                                <span class="request-details__badge request-details__badge--sick">Больничный</span>
                            @endif
                        </div>
                    </div>
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">Период:</div>
                        <div class="request-details__info-value">
                            {{ \Carbon\Carbon::parse($leaveRequest->date_start)->format('d.m.Y') }} — {{ \Carbon\Carbon::parse($leaveRequest->date_end)->format('d.m.Y') }}
                            ({{ \Carbon\Carbon::parse($leaveRequest->date_start)->diffInDays(\Carbon\Carbon::parse($leaveRequest->date_end)) + 1 }} дн.)
                        </div>
                    </div>
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">Причина:</div>
                        <div class="request-details__info-value">{{ $leaveRequest->reason ?? '—' }}</div>
                    </div>
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">Статус:</div>
                        <div class="request-details__info-value">
                            @if($leaveRequest->status === 'new')
                                <span class="request-details__badge request-details__badge--new">Новая</span>
                            @elseif($leaveRequest->status === 'approved')
                                <span class="request-details__badge request-details__badge--approved">Одобрена</span>
                            @elseif($leaveRequest->status === 'rejected')
                                <span class="request-details__badge request-details__badge--rejected">Отклонена</span>
                            @endif
                        </div>
                    </div>
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">Дата подачи:</div>
                        <div class="request-details__info-value">{{ $leaveRequest->created_at->format('d.m.Y H:i') }}</div>
                    </div>
                    @if($leaveRequest->hr_comment)
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">Комментарий HR:</div>
                        <div class="request-details__info-value">{{ $leaveRequest->hr_comment }}</div>
                    </div>
                    @endif
                    @if($leaveRequest->document_path)
                    <div class="request-details__info-row">
                        <div class="request-details__info-label">Прикрепленный документ:</div>
                        <div class="request-details__info-value">
                            <a href="{{ asset('storage/' . $leaveRequest->document_path) }}" target="_blank" class="request-details__document-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                                Просмотреть документ
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        @if($leaveRequest->status === 'new')
        <div class="request-details__update-section">
            <h3 class="request-details__section-title">Обновить статус заявки</h3>
            
            @if(Auth::user()->isAdmin() || $leaveRequest->user_id !== Auth::id())
            <form action="{{ route('hr.leave_requests.update', $leaveRequest) }}" method="POST" class="request-details__form">
                @csrf
                @method('PUT')
                
                <div class="request-details__form-row">
                    <label for="status" class="request-details__form-label">Статус:</label>
                    <div class="request-details__form-radio-group">
                        <label class="request-details__radio-label">
                            <input type="radio" name="status" value="approved" class="request-details__radio">
                            <span class="request-details__radio-text">Одобрить</span>
                        </label>
                        <label class="request-details__radio-label">
                            <input type="radio" name="status" value="rejected" class="request-details__radio">
                            <span class="request-details__radio-text">Отклонить</span>
                        </label>
                    </div>
                </div>
                
                <div class="request-details__form-row">
                    <label for="hr_comment" class="request-details__form-label">Комментарий HR:</label>
                    <textarea id="hr_comment" name="hr_comment" class="request-details__textarea" placeholder="Введите комментарий к решению (необязательно)">{{ old('hr_comment', $leaveRequest->hr_comment) }}</textarea>
                </div>
                
                <div class="request-details__form-actions">
                    <button type="submit" class="request-details__submit-btn">Сохранить решение</button>
                </div>
            </form>
            @else
            <div class="request-details__restricted-notice">
                <div class="request-details__restricted-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                    </svg>
                </div>
                <p class="request-details__restricted-message">
                    Вы не можете одобрять или отклонять свои собственные заявки.
                </p>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>

<style>
.request-details {
    background: var(--color-white);
    border-radius: 12px;
    box-shadow: 0 2px 16px rgba(36,36,36,0.07);
    padding: 2rem 2.2rem 2.2rem 2.2rem;
    border: 1.5px solid var(--color-light-gray);
    margin-bottom: 2rem;
}

.request-details__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.request-details__title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--color-black);
    margin: 0;
}

.request-details__back-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    background-color: #f0f0f0;
    color: #555;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.request-details__back-btn:hover {
    background-color: #e0e0e0;
    transform: translateY(-2px);
}

.request-details__content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.request-details__info-section {
    display: flex;
    gap: 2rem;
}

.request-details__employee-card,
.request-details__request-card {
    flex: 1;
    background-color: #f9f9f9;
    border-radius: 10px;
    padding: 1.5rem;
    border: 1px solid #eee;
}

.request-details__section-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--color-black);
    margin: 0 0 1.2rem 0;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid #eee;
}

.request-details__info-row {
    display: flex;
    margin-bottom: 0.8rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid #f0f0f0;
}

.request-details__info-row:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.request-details__info-label {
    width: 120px;
    min-width: 120px;
    font-weight: 500;
    color: #666;
}

.request-details__info-value {
    flex: 1;
    font-weight: 600;
    color: #333;
}

.request-details__link {
    color: var(--color-primary);
    text-decoration: none;
    transition: color 0.2s;
}

.request-details__link:hover {
    color: var(--color-accent);
    text-decoration: underline;
}

.request-details__badge {
    display: inline-block;
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9em;
}

.request-details__badge--vacation {
    background-color: #2196F3;
    color: white;
}

.request-details__badge--business_trip {
    background-color: #9C27B0;
    color: white;
}

.request-details__badge--sick {
    background-color: #FF9800;
    color: white;
}

.request-details__badge--new {
    background-color: #FFC107;
    color: #000;
}

.request-details__badge--approved {
    background-color: #4CAF50;
    color: white;
}

.request-details__badge--rejected {
    background-color: #F44336;
    color: white;
}

.request-details__update-section {
    background-color: #f9f9f9;
    border-radius: 10px;
    padding: 1.5rem;
    border: 1px solid #eee;
}

.request-details__form {
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
}

.request-details__form-row {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.request-details__form-label {
    font-weight: 500;
    color: #555;
}

.request-details__form-radio-group {
    display: flex;
    gap: 1.5rem;
}

.request-details__radio-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.request-details__radio {
    accent-color: var(--color-primary);
    width: 1.1em;
    height: 1.1em;
    margin: 0;
}

.request-details__radio-text {
    font-weight: 500;
    font-size: 0.95rem;
}

.request-details__textarea {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.95rem;
    resize: vertical;
    min-height: 100px;
    font-family: inherit;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.request-details__textarea:focus {
    border-color: var(--color-primary);
    outline: none;
    box-shadow: 0 0 0 2px rgba(243, 112, 50, 0.15);
}

.request-details__form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 1rem;
}

.request-details__submit-btn {
    padding: 0.7rem 1.5rem;
    background-color: var(--color-primary);
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.request-details__submit-btn:hover {
    background-color: var(--color-accent);
    transform: translateY(-2px);
}

.request-details__document-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background-color: #f0f0f0;
    color: #555;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
}

.request-details__document-link:hover {
    background-color: var(--color-primary);
    color: white;
    transform: translateY(-2px);
}

.request-details__restricted-notice {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.2rem;
    background-color: #fff8f8;
    border-radius: 10px;
    border: 1px solid #ffcdd2;
    margin-top: 0.5rem;
}

.request-details__restricted-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    color: #e57373;
}

.request-details__restricted-message {
    font-size: 0.95rem;
    color: #d32f2f;
    margin: 0;
    font-weight: 500;
}

/* Адаптивная верстка */
@media (max-width: 900px) {
    .request-details {
        padding: 1.5rem 1rem;
    }
    
    .request-details__header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .request-details__info-section {
        flex-direction: column;
    }
}
</style>
@endsection 