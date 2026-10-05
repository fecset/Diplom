<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // Просмотр табеля за период (по умолчанию текущий месяц)
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m'));
        $start = Carbon::parse($date.'-01')->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $query = User::with('department')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->orderBy('departments.name')
            ->orderBy('users.name')
            ->select('users.*');

        // Если пользователь не админ и не HR, показываем только сотрудников его отдела
        if (!Auth::user()->isAdmin() && !Auth::user()->isHrSpecialist()) {
            $query->where('users.department_id', Auth::user()->department_id);
        }
        
        $users = $query->get();
        
        // Устанавливаем явку на текущий день для всех пользователей, если нет записи
        $today = Carbon::today()->format('Y-m-d');
        $currentMonth = Carbon::today()->format('Y-m');
        
        // Проверяем, что выбранный месяц - текущий
        if ($date === $currentMonth) {
            $this->setDefaultAttendanceForToday($users, $today);
        }
        
        $attendances = Attendance::whereBetween('date', [$start, $end])->get()->groupBy(['user_id', 'date']);

        return view('attendances.index', compact('users', 'attendances', 'start', 'end'));
    }

    // Сохранение/редактирование явки (HR/админ)
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,vacation,sick_leave',
            'comment' => 'nullable|string|max:255',
        ]);

        Attendance::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'date' => $request->date,
            ],
            [
                'status' => $request->status,
                'comment' => $request->comment,
            ]
        );

        return back()->with('success', 'Данные успешно сохранены!');
    }
    
    /**
     * Устанавливает статус "present" (явка) для всех пользователей на сегодняшний день,
     * если у них нет записи на этот день.
     *
     * @param \Illuminate\Database\Eloquent\Collection $users Коллекция пользователей
     * @param string $today Текущая дата в формате Y-m-d
     * @return void
     */
    private function setDefaultAttendanceForToday($users, $today)
    {
        foreach ($users as $user) {
            // Проверяем, существует ли запись для пользователя на сегодня
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->first();
            
            // Если записи нет, создаем с статусом "present"
            if (!$attendance) {
                Attendance::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'status' => 'present', // Устанавливаем статус "явка" по умолчанию
                    'comment' => null,
                ]);
            }
        }
    }
}
