<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\LeaveRequest;
use App\Services\LeaveWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrLeaveRequestController extends Controller
{
    /**
     * Конструктор с проверкой прав доступа
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:hr_specialist,admin');
    }

    /**
     * Показать список всех заявок для управления
     */
    public function index(Request $request)
    {
        $request->validate(['name' => 'nullable|string|max:255', 'department' => 'nullable|integer|exists:departments,id', 'date_from' => 'nullable|date_format:Y-m-d', 'date_to' => 'nullable|date_format:Y-m-d']);
        $query = LeaveRequest::with(['user.department', 'user.position'])->orderBy('created_at', 'desc');

        // Фильтрация по типу заявки
        if ($request->has('type') && in_array($request->type, ['vacation', 'sick_leave', 'business_trip'])) {
            $query->where('type', $request->type);
        }

        // Фильтрация по статусу
        if ($request->has('status') && in_array($request->status, ['new', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Фильтрация по имени сотрудника
        if ($request->has('name') && $request->name) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->name.'%');
            });
        }

        // Фильтрация по департаменту
        if ($request->has('department') && $request->department) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }

        // Фильтрация по дате
        if ($request->has('date_from') && $request->date_from) {
            $query->where('date_start', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date_end', '<=', $request->date_to);
        }

        $requests = $query->paginate(20)->withQueryString();
        $pendingCount = LeaveRequest::where('status', 'new')->count();

        // Получаем список всех отделов для фильтра через связь с таблицей departments
        $departments = Department::orderBy('name')->get();

        return view('hr.leave_requests.index', compact('requests', 'departments', 'pendingCount'));
    }

    /**
     * Показать детали заявки
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user.position', 'user.department']);

        return view('hr.leave_requests.show', compact('leaveRequest'));
    }

    /**
     * Обновить статус заявки (одобрить или отклонить)
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'hr_comment' => 'nullable|string|max:255',
        ]);

        // Проверяем, не пытается ли пользователь одобрить свою собственную заявку
        $currentUser = Auth::user();

        if ($leaveRequest->user_id === $currentUser->id && ! $currentUser->isAdmin()) {
            return redirect()->route('hr.leave_requests.index')
                ->with('error', 'Вы не можете одобрять или отклонять свои собственные заявки. Это может сделать только администратор.');
        }

        app(LeaveWorkflow::class)->decide($leaveRequest, $currentUser, $request->status, $request->hr_comment);

        return redirect()->route('hr.leave_requests.index')
            ->with('success', 'Статус заявки успешно обновлен.');
    }

    /**
     * Показать страницу с фильтром только по заявкам на отпуск
     */
    public function vacations(Request $request)
    {
        $request->merge(['type' => 'vacation']);

        return $this->index($request);
    }

    /**
     * Показать страницу с фильтром только по заявкам на больничный
     */
    public function sickLeaves(Request $request)
    {
        $request->merge(['type' => 'sick_leave']);

        return $this->index($request);
    }

    /**
     * Показать страницу с фильтром только по заявкам на командировку
     */
    public function businessTrips(Request $request)
    {
        $request->merge(['type' => 'business_trip']);

        return $this->index($request);
    }

    /**
     * Показать страницу с фильтром только по новым заявкам
     */
    public function pending(Request $request)
    {
        $request->merge(['status' => 'new']);

        return $this->index($request);
    }
}
