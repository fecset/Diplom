<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $leaveRequestStatuses = LeaveRequest::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        // Подготовка данных для графиков (пока заглушка)
        $leaveRequestsByType = LeaveRequest::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();

        // Количество сотрудников по отделам
        $usersByDepartment = User::select('department_id', DB::raw('count(*) as total'))
            ->with('department:id,name') // Загружаем название отдела
            ->groupBy('department_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->department->name ?? 'Без отдела' => $item->total];
            })
            ->all();

        // Количество сотрудников по должностям
        $usersByPosition = User::select('position_id', DB::raw('count(*) as total'))
            ->with('position:id,name') // Загружаем название должности
            ->groupBy('position_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->position->name ?? 'Без должности' => $item->total];
            })
            ->all();

        $yearSql = DB::getDriverName() === 'sqlite' ? "CAST(strftime('%Y', created_at) AS INTEGER)" : 'YEAR(created_at)';
        $monthSql = DB::getDriverName() === 'sqlite' ? "CAST(strftime('%m', created_at) AS INTEGER)" : 'MONTH(created_at)';
        $first = now()->startOfMonth()->subMonths(11);
        $counts = LeaveRequest::selectRaw("{$yearSql} as year, {$monthSql} as month, type, count(*) as total")
            ->where('created_at', '>=', $first)->groupBy('year', 'month', 'type')->get()->keyBy(fn ($r) => $r->year.'-'.$r->month.'-'.$r->type);
        $leaveRequestsTrend = collect();
        for ($i = 0; $i < 12; $i++) {
            $month = $first->copy()->addMonths($i);
            foreach (['vacation', 'sick_leave', 'business_trip'] as $type) {
                $leaveRequestsTrend->push(['year' => $month->year, 'month' => $month->month, 'type' => $type, 'total' => $counts->get($month->year.'-'.$month->month.'-'.$type)?->total ?? 0]);
            }
        }

        // Статистика посещаемости за текущий месяц
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $attendanceStatsLastMonth = Attendance::select('status', DB::raw('count(*) as total'))
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['present', 'absent', 'vacation', 'sick_leave'])
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return view('admin.analytics.index', compact('totalUsers', 'totalDepartments', 'totalPositions', 'leaveRequestStatuses', 'leaveRequestsByType', 'usersByDepartment', 'usersByPosition', 'leaveRequestsTrend', 'attendanceStatsLastMonth'));
    }
}
