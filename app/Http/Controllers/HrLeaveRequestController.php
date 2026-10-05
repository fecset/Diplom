<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Notification;
use App\Models\Department;

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
        $query = LeaveRequest::with('user')->orderBy('created_at', 'desc');

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
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        // Фильтрация по департаменту
        if ($request->has('department') && $request->department) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department', $request->department);
            });
        }

        // Фильтрация по дате
        if ($request->has('date_from') && $request->date_from) {
            $query->where('date_start', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date_end', '<=', $request->date_to);
        }

        $requests = $query->paginate(20);

        // Получаем список всех отделов для фильтра через связь с таблицей departments
        $departments = Department::orderBy('name')->get();

        return view('hr.leave_requests.index', compact('requests', 'departments'));
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

        if ($leaveRequest->user_id === $currentUser->id && !$currentUser->isAdmin()) {
            return redirect()->route('hr.leave_requests.index')
                ->with('error', 'Вы не можете одобрять или отклонять свои собственные заявки. Это может сделать только администратор.');
        }

        $oldStatus = $leaveRequest->status;
        $leaveRequest->status = $request->status;
        $leaveRequest->hr_comment = $request->hr_comment;
        $leaveRequest->save();

        // Создаем уведомление об изменении статуса заявки
        $notification = new Notification();
        $notification->title = 'Обновление статуса заявки';
        $notification->message = sprintf(
            'Ваша заявка на %s %s %s. %s',
            $leaveRequest->type === 'vacation' ? 'отпуск' : ($leaveRequest->type === 'sick_leave' ? 'больничный' : 'командировка'),
            $leaveRequest->date_start,
            $request->status === 'approved' ? 'одобрена' : 'отклонена',
            $request->hr_comment ? 'Комментарий HR: ' . $request->hr_comment : ''
        );
        $notification->type = $request->status === 'approved' ? 'info' : 'warning';
        $notification->created_by = $currentUser->id;
        $notification->is_global = false;
        $notification->target_users = [$leaveRequest->user_id];
        $notification->save();

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
