<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        $request->validate(['title' => 'nullable|string|max:255', 'type' => 'nullable|in:info,warning,important', 'status' => 'nullable|in:active,inactive', 'is_global' => 'nullable|in:true,false']);
        $query = Notification::query();

        // Фильтрация по типу
        if ($request->has('type') && in_array($request->type, ['info', 'warning', 'important'])) {
            $query->where('type', $request->type);
        }

        // Фильтрация по статусу
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>', now());
                })->where(function ($q) {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                });
            } elseif ($request->status === 'inactive') {
                $query->where(function ($q) {
                    $q->where('is_active', false)->orWhere('end_date', '<', now())
                        ->orWhere('start_date', '>', now());
                });
            }
        }

        // Фильтрация по глобальности
        if ($request->filled('is_global')) {
            $query->where('is_global', $request->is_global === 'true');
        }

        // Фильтрация по заголовку
        if ($request->has('title') && $request->title) {
            $query->where('title', 'like', '%'.$request->title.'%');
        }

        // Сортировка
        $sortField = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSortFields = ['title', 'type', 'created_at', 'start_date', 'end_date'];
        if (! in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        $query->orderBy($sortField, $sortOrder);

        $showAll = false;
        $notifications = $query->paginate(8)->withQueryString();

        return view('notifications.index', compact('notifications', 'sortField', 'sortOrder', 'showAll'));
    }

    /**
     * Показывает форму для создания нового уведомления
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('notifications.create', ['departments' => $departments, 'users' => User::orderBy('name')->get()]);
    }

    /**
     * Сохраняет новое уведомление
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:10000',
            'type' => 'required|in:info,warning,important',
            'is_global' => 'boolean',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('start_date'), 'after_or_equal:start_date')],
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:admin,hr_specialist,employee',
            'target_departments' => 'nullable|array',
            'target_departments.*' => 'integer|exists:departments,id',
            'target_users' => 'nullable|array',
            'target_users.*' => 'exists:users,id',
        ], [
            'title.required' => 'Введите заголовок уведомления',
            'message.required' => 'Введите текст уведомления',
            'type.required' => 'Выберите тип уведомления',
            'end_date.after_or_equal' => 'Дата окончания должна быть позже даты начала',
        ]);

        $notification = new Notification;
        $notification->title = $validated['title'];
        $notification->message = $validated['message'];
        $notification->type = $validated['type'];
        $notification->is_global = $request->boolean('is_global');
        $notification->created_by = Auth::id();

        if ($request->filled('start_date')) {
            $notification->start_date = Carbon::parse($validated['start_date']);
        }

        if ($request->filled('end_date')) {
            $notification->end_date = Carbon::parse($validated['end_date'])->endOfDay();
        }

        if (! $notification->is_global) {
            $notification->target_roles = $request->input('target_roles');
            $notification->target_departments = array_map('intval', (array) $request->input('target_departments', []));
            $notification->target_users = array_map('intval', (array) $request->input('target_users', []));
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
        $departments = Department::orderBy('name')->get();

        return view('notifications.edit', ['notification' => $notification, 'departments' => $departments, 'users' => User::orderBy('name')->get()]);
    }

    /**
     * Обновляет уведомление
     */
    public function update(Request $request, Notification $notification)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:10000',
            'type' => 'required|in:info,warning,important',
            'is_global' => 'boolean',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('start_date'), 'after_or_equal:start_date')],
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'in:admin,hr_specialist,employee',
            'target_departments' => 'nullable|array',
            'target_departments.*' => 'integer|exists:departments,id',
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
        $notification->is_global = $request->boolean('is_global');
        $notification->is_active = $request->boolean('is_active');

        if ($request->filled('start_date')) {
            $notification->start_date = Carbon::parse($validated['start_date']);
        } else {
            $notification->start_date = null;
        }

        if ($request->filled('end_date')) {
            $notification->end_date = Carbon::parse($validated['end_date'])->endOfDay();
        } else {
            $notification->end_date = null;
        }

        if (! $notification->is_global) {
            $notification->target_roles = $request->input('target_roles', []);
            $notification->target_departments = array_map('intval', (array) $request->input('target_departments', []));
            if ($request->has('audience_present') || $request->has('target_users')) {
                $notification->target_users = array_map('intval', (array) $request->input('target_users', []));
            }
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
    public function show(Notification $notification)
    {
        return redirect()->route('notifications.edit', $notification);
    }

    public function getUserNotifications(Request $request)
    {
        $query = Notification::visibleTo($request->user());
        $unread = (clone $query)->whereDoesntHave('reads', fn ($q) => $q->where('users.id', $request->user()->id))->count();
        $page = $query->with(['reads' => fn ($q) => $q->where('users.id', $request->user()->id)])->latest()->paginate(20);

        return response()->json(['data' => $page->getCollection()->map(fn ($n) => [
            'id' => $n->id, 'title' => $n->title, 'message' => $n->message, 'type' => $n->type, 'created_at' => $n->created_at, 'is_read' => $n->isReadByUser($request->user()->id),
        ]), 'next_page_url' => $page->nextPageUrl(), 'unread_count' => $unread]);
    }

    public function markAsRead(Request $request)
    {
        $data = $request->validate(['notification_ids' => 'sometimes|array|max:100', 'notification_ids.*' => 'integer|min:1']);
        $query = Notification::visibleTo($request->user());
        // An explicit empty array means none, an absent field means all.
        if (array_key_exists('notification_ids', $data)) {
            $query->whereIn('id', $data['notification_ids']);
        }
        $query->select('notifications.*')->chunkById(100, function ($rows) use ($request) {
            foreach ($rows as $row) {
                $row->markAsReadByUser($request->user()->id);
            }
        });

        return response()->json(['success' => true]);
    }
}
