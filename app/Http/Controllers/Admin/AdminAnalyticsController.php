<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Position;
use App\Models\LeaveRequest;
use App\Models\User;

class AdminAnalyticsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Получение общих статистических данных
        $totalUsers = User::count();
        $totalDepartments = Department::count();
        $totalPositions = Position::count();
        
        // Количество заявок по статусам
        $leaveRequestStatuses = LeaveRequest::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        // Подготовка данных для графиков (пока заглушка)
        $leaveRequestsByType = LeaveRequest::select('type', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();

        // Количество сотрудников по отделам
        $usersByDepartment = User::select('department_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->with('department:id,name') // Загружаем название отдела
            ->groupBy('department_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->department->name ?? 'Без отдела' => $item->total];
            })
            ->all();

        // Количество сотрудников по должностям
        $usersByPosition = User::select('position_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->with('position:id,name') // Загружаем название должности
            ->groupBy('position_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->position->name ?? 'Без должности' => $item->total];
            })
            ->all();

        // Динамика заявок по месяцам и типам за последний год
        $leaveRequestsTrend = LeaveRequest::select(
                \Illuminate\Support\Facades\DB::raw('YEAR(created_at) as year'),
                \Illuminate\Support\Facades\DB::raw('MONTH(created_at) as month'),
                'type',
                \Illuminate\Support\Facades\DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', \Carbon\Carbon::now()->subYear())
            ->groupBy('year', 'month', 'type')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Статистика посещаемости за текущий месяц
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        $endOfMonth = \Carbon\Carbon::now()->endOfMonth();

        $attendanceStatsLastMonth = \App\Models\Attendance::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['present', 'absent', 'vacation', 'sick_leave'])
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        // Добавляем отладочную информацию
        \Illuminate\Support\Facades\Log::info('Attendance Stats:', [
            'date_range' => [
                'start' => $startOfMonth->format('Y-m-d'),
                'end' => $endOfMonth->format('Y-m-d')
            ],
            'stats' => $attendanceStatsLastMonth
        ]);

        return view('admin.analytics.index', compact('totalUsers', 'totalDepartments', 'totalPositions', 'leaveRequestStatuses', 'leaveRequestsByType', 'usersByDepartment', 'usersByPosition', 'leaveRequestsTrend', 'attendanceStatsLastMonth'));
    }
}
