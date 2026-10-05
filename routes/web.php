<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HrController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HrLeaveRequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Главная страница / Логин
Route::get('/', function () {
    if (Auth::check()) {
        // Перенаправляем всех пользователей в личный кабинет
        return redirect()->route('dashboard');
    }
    return redirect()->route('login'); // Если не аутентифицирован, показываем страницу входа
})->name('home');

// Маршруты аутентификации
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('login', [LoginController::class, 'login'])->middleware('guest');
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Маршруты для всех аутентифицированных пользователей (личный кабинет)
Route::middleware(['auth', 'role:employee,hr_specialist,admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('leave_requests', LeaveRequestController::class)->only(['index', 'create', 'store']);
    Route::get('leave_requests/vacation', [LeaveRequestController::class, 'vacation'])->name('leave_requests.vacation');
    Route::get('leave_requests/sick_leave', [LeaveRequestController::class, 'sickLeave'])->name('leave_requests.sick_leave');

    // Добавляем специальные маршруты для создания заявок
    Route::get('leave_requests/vacation/create', [LeaveRequestController::class, 'create'])->defaults('type', 'vacation')->name('leave_requests.vacation.create');
    Route::get('leave_requests/sick_leave/create', [LeaveRequestController::class, 'create'])->defaults('type', 'sick_leave')->name('leave_requests.sick_leave.create');
    Route::get('leave_requests/business_trip/create', [LeaveRequestController::class, 'create'])->defaults('type', 'business_trip')->name('leave_requests.business_trip.create');
    Route::get('leave_requests/business_trip', [LeaveRequestController::class, 'businessTrip'])->name('leave_requests.business_trip');

    // Маршруты профиля пользователя
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // Маршрут для скачивания справки с места работы
    Route::get('/profile/download-certificate', [App\Http\Controllers\ProfileController::class, 'downloadCertificate'])->name('profile.downloadCertificate');
});
Route::get('attendance', [AttendanceController::class, 'index'])->name('hr.attendance.index');
// Маршруты для HR-специалистов и администраторов
Route::middleware(['auth', 'role:hr_specialist,admin'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/', [HrController::class, 'index'])->name('index');
    // Другие маршруты для HR...

    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // Маршруты для кадрового учёта
    Route::get('personnel', [HrController::class, 'personnel'])->name('personnel.index');
    Route::get('personnel/create', [HrController::class, 'createEmployee'])->name('personnel.create');

    // Маршруты для работы с сотрудниками (с ограничением для администраторов)
    Route::middleware(['admin.role'])->group(function () {
        Route::post('personnel', [HrController::class, 'storeEmployee'])->name('personnel.store');
        Route::get('personnel/{user}/edit', [HrController::class, 'editEmployee'])->name('personnel.edit');
        Route::put('personnel/{user}', [HrController::class, 'updateEmployee'])->name('personnel.update');
        Route::delete('personnel/{user}', [HrController::class, 'deleteEmployee'])->name('personnel.delete');
    });

    // Просмотр сотрудников без ограничений для HR-специалистов
    Route::get('personnel/{user}', [HrController::class, 'showEmployee'])->name('personnel.show');

    // Маршруты для управления заявками на отпуск и больничный
    Route::get('leave-requests', [HrLeaveRequestController::class, 'index'])->name('leave_requests.index');
    Route::get('leave-requests/vacations', [HrLeaveRequestController::class, 'vacations'])->name('leave_requests.vacations');
    Route::get('leave-requests/sick-leaves', [HrLeaveRequestController::class, 'sickLeaves'])->name('leave_requests.sick_leaves');
    Route::get('leave-requests/business-trips', [HrLeaveRequestController::class, 'businessTrips'])->name('leave_requests.business_trips');
    Route::get('leave-requests/pending', [HrLeaveRequestController::class, 'pending'])->name('leave_requests.pending');
    Route::get('leave-requests/{leaveRequest}', [HrLeaveRequestController::class, 'show'])->name('leave_requests.show');
    Route::put('leave-requests/{leaveRequest}', [HrLeaveRequestController::class, 'update'])->name('leave_requests.update');
});

// Маршруты для уведомлений (доступны HR-специалистам и администраторам)
Route::middleware(['auth', 'role:hr_specialist,admin'])->group(function () {
    Route::resource('notifications', NotificationController::class);
});

// API для получения уведомлений для текущего пользователя
Route::middleware(['auth'])->get('/api/notifications', [NotificationController::class, 'getUserNotifications'])
    ->name('api.notifications');

// API для отметки уведомлений как прочитанных
Route::middleware(['auth'])->post('/api/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])
    ->name('api.notifications.mark_as_read');

// Маршруты только для администраторов
Route::middleware(['auth', 'role:admin'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('positions', PositionController::class);
    Route::resource('notifications', NotificationController::class);
    Route::resource('attendance', AttendanceController::class)->only(['index', 'store', 'update']);
    Route::get('/leave-requests', [HrLeaveRequestController::class, 'index'])->name('leave_requests.index');
    Route::get('/leave-requests/pending', [HrLeaveRequestController::class, 'pending'])->name('leave_requests.pending');
    Route::get('/leave-requests/vacations', [HrLeaveRequestController::class, 'vacations'])->name('leave_requests.vacations');
    Route::get('/leave-requests/sick-leaves', [HrLeaveRequestController::class, 'sickLeaves'])->name('leave_requests.sick_leaves');
    Route::get('/leave-requests/business-trips', [HrLeaveRequestController::class, 'businessTrips'])->name('leave_requests.business_trips');
    Route::put('/leave-requests/{leaveRequest}', [HrLeaveRequestController::class, 'update'])->name('leave_requests.update');
    Route::get('/leave-requests/{leaveRequest}', [HrLeaveRequestController::class, 'show'])->name('leave_requests.show');
    Route::resource('personnel', HrController::class);
    Route::get('/analytics', [App\Http\Controllers\Admin\AdminAnalyticsController::class, 'index'])->name('analytics.index');
});
