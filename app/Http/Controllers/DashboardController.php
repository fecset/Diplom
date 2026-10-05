<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\Notification;
use Carbon\Carbon;

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
            $experience = [
                'years' => $now->diffInYears($hiredDate),
                'months' => $now->copy()->subYears($now->diffInYears($hiredDate))->diffInMonths($hiredDate),
                'days' => $now->copy()->subYears($now->diffInYears($hiredDate))
                    ->subMonths($now->copy()->subYears($now->diffInYears($hiredDate))->diffInMonths($hiredDate))
                    ->diffInDays($hiredDate)
            ];
        }
        
        // Получаем оставшиеся дни отпуска (заглушка, в реальной системе тут будет логика расчета)
        $vacationDaysLeft = 28; // Стандартный отпуск в РФ
        $usedVacationDays = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'vacation')
            ->where('status', 'approved')
            ->whereYear('date_start', Carbon::now()->year)
            ->get()
            ->sum(function($request) {
                return Carbon::parse($request->date_start)->diffInDays(Carbon::parse($request->date_end)) + 1;
            });
        
        $vacationDaysLeft -= $usedVacationDays;
        
        // Получаем уведомления для пользователя
        $allNotifications = Notification::where('is_active', true)->get();
        
        $userNotifications = [];
        foreach ($allNotifications as $notification) {
            if ($notification->isVisibleToUser($user)) {
                $userNotifications[] = $notification;
            }
        }
        
        // Ограничиваем список 3 последними уведомлениями для отображения в дашборде
        $userNotifications = collect($userNotifications)->sortByDesc('created_at')->take(3);
        
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
