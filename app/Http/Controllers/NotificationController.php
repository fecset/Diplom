<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:hr_specialist,admin')->except(['getUserNotifications', 'markAsRead']);
    }

    /**
     * Отображает список уведомлений
     */
    public function index(Request $request)
    {
        $query = Notification::query();

        // Фильтрация по типу
        if ($request->has('type') && in_array($request->type, ['info', 'warning', 'important'])) {
            $query->where('type', $request->type);
        }

        // Фильтрация по статусу
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where(function($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>', now());
                })->where(function($q) {
                    $q->whereNull('start_date')
                      ->orWhere('start_date', '<=', now());
                });
            } elseif ($request->status === 'inactive') {
                $query->where(function($q) {
                    $q->where('end_date', '<', now())
                      ->orWhere('start_date', '>', now());
                });
            }
        }

        // Фильтрация по глобальности
        if ($request->has('is_global')) {
            $query->where('is_global', $request->is_global === 'true');
        }

        // Фильтрация по заголовку
        if ($request->has('title') && $request->title) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Сортировка
        $sortField = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $allowedSortFields = ['title', 'type', 'created_at', 'start_date', 'end_date'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        $query->orderBy($sortField, $sortOrder);

        // Определяем, есть ли фильтры/поиск/сортировка
        $hasFilters = $request->filled('type') || $request->filled('status') || $request->filled('is_global') || $request->filled('title');
        $isDefaultSort = ($sortField === 'created_at' && $sortOrder === 'desc');
        $showAll = $hasFilters || !$isDefaultSort;

        if ($showAll && $request->has('page')) {
            // Удаляем параметр page из URL и делаем редирект
            $params = $request->except('page');
            return redirect()->route('notifications.index', $params);
        }
        if ($showAll) {
            $notifications = $query->get();
        } else {
            $notifications = $query->paginate(8)->withQueryString();
        }

        return view('notifications.index', compact('notifications', 'sortField', 'sortOrder', 'showAll'));
    }

    /**
     * Показывает форму для создания нового уведомления
     */
    public function create()
    {
        $departments = \App\Models\Department::orderBy('name')->get();
                         
        return view('notifications.create', compact('departments'));
    }

    /**
     * Сохраняет новое уведомление
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,important',
            'is_global' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:admin,hr_specialist,employee',
            'target_departments' => 'nullable|array',
            'target_departments.*' => 'string',
            'target_users' => 'nullable|array',
            'target_users.*' => 'exists:users,id',
        ], [
            'title.required' => 'Введите заголовок уведомления',
            'message.required' => 'Введите текст уведомления',
            'type.required' => 'Выберите тип уведомления',
            'end_date.after_or_equal' => 'Дата окончания должна быть позже даты начала',
        ]);

        $notification = new Notification();
        $notification->title = $validated['title'];
        $notification->message = $validated['message'];
        $notification->type = $validated['type'];
        $notification->is_global = $request->has('is_global');
        $notification->created_by = Auth::id();
        
        if ($request->filled('start_date')) {
            $notification->start_date = Carbon::parse($validated['start_date']);
        }
        
        if ($request->filled('end_date')) {
            $notification->end_date = Carbon::parse($validated['end_date']);
        }
        
        if (!$notification->is_global) {
            $notification->target_roles = $request->input('target_roles');
            $notification->target_departments = $request->input('target_departments');
            $notification->target_users = $request->input('target_users');
        }
        
        $notification->save();
        
        return redirect()->route('notifications.index')
                         ->with('success', 'Уведомление успешно создано');
    }

    /**
     * Показывает форму для редактирования уведомления
     */
    public function edit(Notification $notification)
    {
        $departments = \App\Models\Department::orderBy('name')->get();
                         
        return view('notifications.edit', compact('notification', 'departments'));
    }

    /**
     * Обновляет уведомление
     */
    public function update(Request $request, Notification $notification)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,important',
            'is_global' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:admin,hr_specialist,employee',
            'target_departments' => 'nullable|array',
            'target_departments.*' => 'string',
            'target_users' => 'nullable|array',
            'target_users.*' => 'exists:users,id',
            'is_active' => 'boolean',
        ], [
            'title.required' => 'Введите заголовок уведомления',
            'message.required' => 'Введите текст уведомления',
            'type.required' => 'Выберите тип уведомления',
            'end_date.after_or_equal' => 'Дата окончания должна быть позже даты начала',
        ]);

        $notification->title = $validated['title'];
        $notification->message = $validated['message'];
        $notification->type = $validated['type'];
        $notification->is_global = $request->has('is_global');
        $notification->is_active = $request->has('is_active');
        
        if ($request->filled('start_date')) {
            $notification->start_date = Carbon::parse($validated['start_date']);
        } else {
            $notification->start_date = null;
        }
        
        if ($request->filled('end_date')) {
            $notification->end_date = Carbon::parse($validated['end_date']);
        } else {
            $notification->end_date = null;
        }
        
        if (!$notification->is_global) {
            $notification->target_roles = $request->input('target_roles', []);
            $notification->target_departments = $request->input('target_departments', []);
            $notification->target_users = $request->input('target_users', []);
        } else {
            $notification->target_roles = null;
            $notification->target_departments = null;
            $notification->target_users = null;
        }
        
        $notification->save();
        
        return redirect()->route('notifications.index')
                         ->with('success', 'Уведомление успешно обновлено');
    }

    /**
     * Удаляет уведомление
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();
        
        return redirect()->route('notifications.index')
                         ->with('success', 'Уведомление успешно удалено');
    }
    
    /**
     * Возвращает уведомления для текущего пользователя (для отображения в дашборде)
     */
    public function getUserNotifications()
    {
        $user = Auth::user();
        $allNotifications = Notification::where('is_active', true)->get();
        
        $notifications = [];
        foreach ($allNotifications as $notification) {
            if ($notification->isVisibleToUser($user)) {
                $notificationData = $notification->toArray();
                $notificationData['is_read'] = $notification->isReadByUser($user->id);
                $notifications[] = $notificationData;
            }
        }
        
        return response()->json($notifications);
    }
    
    /**
     * Отмечает уведомления как прочитанные для текущего пользователя
     */
    public function markAsRead(Request $request)
    {
        $user = Auth::user();
        $notificationIds = $request->input('notification_ids', []);
        
        if (empty($notificationIds)) {
            // Если не указаны конкретные уведомления, отмечаем все как прочитанные
            $allNotifications = Notification::where('is_active', true)->get();
            
            foreach ($allNotifications as $notification) {
                if ($notification->isVisibleToUser($user)) {
                    $notification->markAsReadByUser($user->id);
                }
            }
            
            return response()->json(['success' => true, 'message' => 'Все уведомления отмечены как прочитанные']);
        } else {
            // Отмечаем как прочитанные только указанные уведомления
            $notifications = Notification::whereIn('id', $notificationIds)->get();
            
            foreach ($notifications as $notification) {
                if ($notification->isVisibleToUser($user)) {
                    $notification->markAsReadByUser($user->id);
                }
            }
            
            return response()->json(['success' => true, 'message' => 'Выбранные уведомления отмечены как прочитанные']);
        }
    }
}
