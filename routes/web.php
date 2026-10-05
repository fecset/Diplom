<?php

use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\HrController;
use App\Http\Controllers\HrLeaveRequestController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'dashboard' : 'login'))->name('home');
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:20,1');
});
Route::post('logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('leave_requests', LeaveRequestController::class)->only(['index', 'create', 'store']);
    foreach (['vacation' => 'vacation', 'sick_leave' => 'sickLeave', 'business_trip' => 'businessTrip'] as $type => $method) {
        Route::get('leave_requests/'.$type, [LeaveRequestController::class, $method])->name('leave_requests.'.$type);
        Route::get('leave_requests/'.$type.'/create', [LeaveRequestController::class, 'create'])->defaults('type', $type)->name('leave_requests.'.$type.'.create');
    }
    Route::get('leave_requests/{leaveRequest}/document', [LeaveRequestController::class, 'document'])->name('leave_requests.document');
    Route::get('attendance', [AttendanceController::class, 'index'])->name('hr.attendance.index');
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    // Personnel records are archived by HR rather than self-deleted.
    Route::get('profile/download-certificate', [ProfileController::class, 'downloadCertificate'])->name('profile.downloadCertificate');
    Route::get('api/notifications', [NotificationController::class, 'getUserNotifications'])->name('api.notifications');
    Route::post('api/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('api.notifications.mark_as_read');
});
$personnelRoutes = function (string $path, string $name) {
    Route::get($path, [HrController::class, 'personnel'])->name($name.'.index');
    Route::get($path.'/create', [HrController::class, 'createEmployee'])->name($name.'.create');
    Route::post($path, [HrController::class, 'storeEmployee'])->middleware('admin.role')->name($name.'.store');
    Route::get($path.'/{user}/edit', [HrController::class, 'editEmployee'])->middleware('admin.role')->name($name.'.edit');
    Route::get($path.'/{user}', [HrController::class, 'showEmployee'])->withTrashed()->name($name.'.show');
    Route::put($path.'/{user}', [HrController::class, 'updateEmployee'])->middleware('admin.role')->name($name.'.update');
    Route::delete($path.'/{user}', [HrController::class, 'deleteEmployee'])->middleware('admin.role')->name($name.'.delete');
};
$leaveRoutes = function () {
    Route::get('leave-requests', [HrLeaveRequestController::class, 'index'])->name('leave_requests.index');
    foreach (['pending' => 'pending', 'vacations' => 'vacations', 'sick-leaves' => 'sickLeaves', 'business-trips' => 'businessTrips'] as $path => $method) {
        Route::get('leave-requests/'.$path, [HrLeaveRequestController::class, $method])->name('leave_requests.'.str_replace('-', '_', $path));
    }
    Route::get('leave-requests/{leaveRequest}', [HrLeaveRequestController::class, 'show'])->name('leave_requests.show');
    Route::put('leave-requests/{leaveRequest}', [HrLeaveRequestController::class, 'update'])->name('leave_requests.update');
};
Route::middleware(['auth', 'role:hr_specialist,admin'])->group(function () use ($personnelRoutes, $leaveRoutes) {
    Route::resource('notifications', NotificationController::class);
    Route::prefix('hr')->name('hr.')->group(function () use ($personnelRoutes, $leaveRoutes) {
        Route::get('/', [HrController::class, 'index'])->name('index');
        Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        $personnelRoutes('personnel', 'personnel');
        $leaveRoutes();
    });
});
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () use ($personnelRoutes, $leaveRoutes) {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    $personnelRoutes('users', 'users');
    $personnelRoutes('personnel', 'personnel');
    Route::resource('departments', DepartmentController::class);
    Route::resource('positions', PositionController::class);
    Route::resource('notifications', NotificationController::class);
    Route::resource('attendance', AttendanceController::class)->only(['index', 'store', 'update']);
    $leaveRoutes();
    Route::get('analytics',[AdminAnalyticsController::class, 'index'])->name('analytics.index');
});
