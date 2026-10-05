<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveWorkflow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
        abort_unless(in_array($type, ['vacation', 'sick_leave', 'business_trip'], true), 404);

        return view('leave_requests.create', compact('type'));
    }

    // Сохранение заявки
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:vacation,sick_leave,business_trip',
            'date_start' => 'required|date_format:Y-m-d',
            'date_end' => 'required|date_format:Y-m-d|after_or_equal:date_start',
            'reason' => 'nullable|string|max:255',
            'document' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
            'destination' => 'required_if:type,business_trip|nullable|string|max:255',
            'purpose' => 'required_if:type,business_trip|nullable|string|max:255',
        ], [
            'type.required' => 'Выберите тип заявки.',
            'date_start.required' => 'Укажите дату начала.',
            'date_end.required' => 'Укажите дату окончания.',
            'date_end.after_or_equal' => 'Дата окончания не может быть раньше даты начала.',
            'document.mimes' => 'Файл должен быть в формате: jpeg, png, pdf.',
            'document.max' => 'Размер файла не должен превышать 5 МБ.',
        ]);

        if (Carbon::parse($request->date_start)->diffInDays(Carbon::parse($request->date_end)) > 366) {
            throw ValidationException::withMessages(['date_end' => 'Период заявки не может превышать 367 дней.']);
        }
        $data = $request->only(['type', 'date_start', 'date_end', 'reason', 'destination', 'purpose']);
        $data['user_id'] = Auth::id();
        $data['status'] = 'new';

        $path = null;
        try {
            DB::transaction(function () use ($request, &$data, &$path) {
                User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
                app(LeaveWorkflow::class)->checkOverlap(Auth::id(), $data['date_start'], $data['date_end']);
                if ($request->hasFile('document')) {
                    $path = $request->file('document')->store('documents', 'private');
                    $data['document_path'] = $path;
                }
                LeaveRequest::create($data);
            });
        } catch (\Throwable $exception) {
            if ($path) {
                Storage::disk('private')->delete($path);
            }
            throw $exception;
        }

        $successMessage = match ($request->type) {
            'vacation' => 'Заявка на отпуск успешно создана.',
            'sick_leave' => 'Заявка на больничный успешно создана.',
            'business_trip' => 'Заявка на командировку успешно создана.',
            default => 'Заявка успешно создана.'
        };

        return redirect()->route('leave_requests.index')
            ->with('success', $successMessage);
    }

    public function document(LeaveRequest $leaveRequest)
    {
        $user = auth()->user();
        abort_unless($leaveRequest->user_id === $user->id || $user->isAdmin() || $user->isHrSpecialist(), 403);
        abort_unless($leaveRequest->document_path && Storage::disk('private')->exists($leaveRequest->document_path), 404);

        return Storage::disk('private')->download($leaveRequest->document_path, 'document-'.$leaveRequest->id.'.'.pathinfo($leaveRequest->document_path, PATHINFO_EXTENSION), ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
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
