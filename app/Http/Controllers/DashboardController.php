<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Services\VacationBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Получаем последние заявки пользователя
        $recentLeaveRequests = LeaveRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Получаем информацию о текущих заявках (в статусе рассмотрения)
        $pendingRequests = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'new')
            ->count();

        // Получаем данные о посещаемости за текущий месяц
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

        // Рассчитываем трудовой стаж
        $experience = null;
        if ($user->hired_at) {
            $hiredDate = Carbon::parse($user->hired_at);
            $now = Carbon::now();
            $interval = $hiredDate->diff($now);
            $experience = ['years' => $interval->y, 'months' => $interval->m, 'days' => $interval->d];
        }

        $vacationDaysLeft = app(VacationBalance::class)->remaining($user, now()->year);
        $userNotifications = Notification::visibleTo($user)->latest()->limit(3)->get();

        return view('dashboard', compact(
            'user',
            'recentLeaveRequests',
            'pendingRequests',
            'attendanceStats',
            'experience',
            'vacationDaysLeft',
            'userNotifications'
        ));
    }
}
