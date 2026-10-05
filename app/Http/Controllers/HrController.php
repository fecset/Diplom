<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HrController extends Controller
{
    public function index()
    {
        // Перенаправляем на страницу кадрового учета
        return redirect()->route('hr.personnel.index');
    }

    /**
     * Показывает список сотрудников (кадровый учёт)
     */
    public function personnel(Request $request)
    {
        $request->validate(['name' => 'nullable|string|max:255', 'department' => 'nullable|integer|exists:departments,id', 'position' => 'nullable|integer|exists:positions,id']);
        $sortField = $request->input('sort', 'name');
        if (! in_array($sortField, ['name', 'department', 'position', 'role', 'hired_at'], true)) {
            $sortField = 'name';
        }
        $sortOrder = $request->input('order') === 'desc' ? 'desc' : 'asc';
        $query = User::with(['department', 'position'])->select('users.*');
        if ($request->filled('name')) {
            $query->where('users.name', 'like', '%'.$request->name.'%');
        }
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }
        if ($request->filled('position')) {
            $query->where('position_id', $request->position);
        }
        if ($sortField === 'department' || $sortField === 'position') {
            $table = $sortField === 'department' ? 'departments' : 'positions';
            $query->leftJoin($table, 'users.'.$sortField.'_id', '=', $table.'.id')->orderBy($table.'.name', $sortOrder);
        } else {
            $query->orderBy('users.'.$sortField, $sortOrder);
        }
        $employees = $query->orderBy('users.id')->paginate(8)->withQueryString();
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();

        return view('hr.personnel.index', compact('employees', 'departments', 'positions', 'sortField', 'sortOrder'));
    }

    /**
     * Показывает форму для добавления нового сотрудника
     */
    public function createEmployee()
    {
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();

        return view('hr.personnel.create', compact('departments', 'positions'));
    }

    /**
     * Сохраняет нового сотрудника в базе данных
     */
    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'nullable|email|max:255|unique:users',
            'phone_number' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'hired_at' => 'nullable|date',
            'role' => 'required|in:employee,hr_specialist,admin',
            'vacation_days_per_year' => 'sometimes|required|integer|min:0|max:366',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Введите ФИО сотрудника',
            'username.required' => 'Введите логин сотрудника',
            'username.unique' => 'Такой логин уже используется',
            'email.unique' => 'Такой email уже используется',
            'department_id.required' => 'Укажите отдел',
            'department_id.exists' => 'Выбранный отдел не найден',
            'position_id.required' => 'Укажите должность',
            'position_id.exists' => 'Выбранная должность не найдена',
            'role.required' => 'Выберите роль',
            'password.required' => 'Введите пароль',
            'password.min' => 'Минимальная длина пароля - 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'department_id' => $validated['department_id'],
            'position_id' => $validated['position_id'],
            'hired_at' => $validated['hired_at'] ?? null,
            'role' => $validated['role'],
            'vacation_days_per_year' => $validated['vacation_days_per_year'] ?? 28,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('hr.personnel.index')
            ->with('success', 'Сотрудник успешно добавлен');
    }

    /**
     * Показывает форму для редактирования сотрудника
     */
    public function editEmployee(User $user)
    {
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();

        return view('hr.personnel.edit', compact('user', 'departments', 'positions'));
    }

    /**
     * Обновляет данные сотрудника
     */
    public function updateEmployee(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'hired_at' => 'nullable|date',
            'role' => 'required|in:employee,hr_specialist,admin',
            'vacation_days_per_year' => 'sometimes|required|integer|min:0|max:366',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'name.required' => 'Введите ФИО сотрудника',
            'username.required' => 'Введите логин сотрудника',
            'username.unique' => 'Такой логин уже используется',
            'email.unique' => 'Такой email уже используется',
            'department_id.required' => 'Укажите отдел',
            'department_id.exists' => 'Выбранный отдел не найден',
            'position_id.required' => 'Укажите должность',
            'position_id.exists' => 'Выбранная должность не найдена',
            'role.required' => 'Выберите роль',
            'password.min' => 'Минимальная длина пароля - 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ]);

        DB::transaction(function () use ($user, $validated) {
            $admins = User::where('role', 'admin')->orderBy('id')->lockForUpdate()->get();
            $current = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_if($current->isAdmin() && ! auth()->user()->isAdmin(), 403);
            if ($current->isAdmin() && $validated['role'] !== 'admin' && $admins->count() <= 1) {
                throw ValidationException::withMessages(['role' => 'Нельзя понизить роль последнего администратора.']);
            }
            if (empty($validated['password'])) {
                unset($validated['password']);
            }
            $current->update($validated);
        }, 3);

        return redirect()->route('hr.personnel.index')
            ->with('success', 'Данные сотрудника успешно обновлены');
    }

    /**
     * Показывает детальную информацию о сотруднике
     */
    public function showEmployee(User $user)
    {
        // Получаем статистику по посещаемости за последний месяц
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $attendanceStats = [
            'present' => 0,
            'absent' => 0,
            'vacation' => 0,
            'sick_leave' => 0,
        ];

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        foreach ($attendances as $attendance) {
            if (isset($attendanceStats[$attendance->status])) {
                $attendanceStats[$attendance->status]++;
            }
        }

        // Получаем заявки на отпуск и больничный
        $leaveRequests = LeaveRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Рассчитываем трудовой стаж
        $experience = null;
        if ($user->hired_at) {
            $hiredDate = Carbon::parse($user->hired_at);
            $now = Carbon::now();
            $interval = $hiredDate->diff($now);
            $experience = ['years' => $interval->y, 'months' => $interval->m, 'days' => $interval->d];
        }

        return view('hr.personnel.show', compact('user', 'attendanceStats', 'leaveRequests', 'experience'));
    }

    /**
     * Удаляет сотрудника
     */
    public function deleteEmployee(User $user)
    {
        // Проверяем, что текущий пользователь имеет права на удаление
        if (! auth()->user()->isAdmin() && ! auth()->user()->isHrSpecialist()) {
            return redirect()->route('hr.personnel.index')
                ->with('error', 'У вас нет прав для удаления сотрудников');
        }

        // Нельзя удалить самого себя
        if ($user->id === auth()->id()) {
            return redirect()->route('hr.personnel.index')
                ->with('error', 'Вы не можете удалить свой аккаунт');
        }

        abort_if($user->isAdmin() && ! auth()->user()->isAdmin(), 403);
        DB::transaction(function () use ($user) {
            $admins = User::where('role', 'admin')->orderBy('id')->lockForUpdate()->get();
            $current = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_if($current->isAdmin() && ! auth()->user()->isAdmin(), 403);
            abort_if($current->isAdmin() && $admins->count() <= 1, 409, 'Нельзя удалить последнего администратора.');
            $current->delete(); // Archive; keep personnel history and creator references.
        }, 3);

        return redirect()->route('hr.personnel.index')
            ->with('success', 'Сотрудник успешно удален');
    }
}
