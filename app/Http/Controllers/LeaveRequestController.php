<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest;

class LeaveRequestController extends Controller
{
    // Список заявок текущего пользователя
    public function index()
    {
        $requests = LeaveRequest::where('user_id', Auth::id())->orderByDesc('created_at')->get();
        return view('leave_requests.index', compact('requests'));
    }

    // Форма подачи заявки
    public function create(Request $request)
    {
        // Получаем тип из запроса или из значения по умолчанию, переданного в маршруте через defaults()
        $type = $request->get('type', $request->route()->defaults['type'] ?? 'vacation');
        return view('leave_requests.create', compact('type'));
    }

    // Сохранение заявки
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:vacation,sick_leave,business_trip',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'reason' => 'nullable|string|max:255',
            'document' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
            'destination' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:255',
        ], [
            'type.required' => 'Выберите тип заявки.',
            'date_start.required' => 'Укажите дату начала.',
            'date_end.required' => 'Укажите дату окончания.',
            'date_end.after_or_equal' => 'Дата окончания не может быть раньше даты начала.',
            'document.mimes' => 'Файл должен быть в формате: jpeg, png, pdf.',
            'document.max' => 'Размер файла не должен превышать 5 МБ.',
        ]);

        $data = $request->only(['type', 'date_start', 'date_end', 'reason', 'destination', 'purpose']);
        $data['user_id'] = Auth::id();
        $data['status'] = 'new';
        
        // Обработка загрузки документа
        if ($request->hasFile('document')) {
            $document = $request->file('document');
            $filename = time() . '_' . $document->getClientOriginalName();
            $document->storeAs('public/documents', $filename);
            $data['document_path'] = 'documents/' . $filename;
        }
        
        LeaveRequest::create($data);

        $successMessage = match($request->type) {
            'vacation' => 'Заявка на отпуск успешно создана.',
            'sick_leave' => 'Заявка на больничный успешно создана.',
            'business_trip' => 'Заявка на командировку успешно создана.',
            default => 'Заявка успешно создана.'
        };

        return redirect()->route('leave_requests.index')
            ->with('success', $successMessage);
    }

    // Заявки только на отпуск
    public function vacation()
    {
        $requests = LeaveRequest::where('user_id', Auth::id())
            ->where('type', 'vacation')
            ->orderByDesc('created_at')->get();
        $type = 'vacation';
        return view('leave_requests.index', compact('requests', 'type'));
    }

    // Заявки только на больничный
    public function sickLeave()
    {
        $requests = LeaveRequest::where('user_id', Auth::id())
            ->where('type', 'sick_leave')
            ->orderByDesc('created_at')->get();
        $type = 'sick_leave';
        return view('leave_requests.index', compact('requests', 'type'));
    }

    // Заявки только на командировку
    public function businessTrip()
    {
        $requests = LeaveRequest::where('user_id', Auth::id())
            ->where('type', 'business_trip')
            ->orderByDesc('created_at')->get();
        $type = 'business_trip';
        return view('leave_requests.index', compact('requests', 'type'));
    }
}
