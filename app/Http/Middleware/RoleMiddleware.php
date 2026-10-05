<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  // Принимаем одну или несколько ролей
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            // Если пользователь не аутентифицирован, перенаправляем на страницу входа
            return redirect('login');
        }

        $user = Auth::user();

        // Временно добавим логирование роли пользователя
        \Illuminate\Support\Facades\Log::info('Checking role for user ID: ' . $user->id . ' with role: ' . $user->role);

        // Проверяем, есть ли у пользователя хотя бы одна из требуемых ролей
        foreach ($roles as $role) {
            if ($role === 'admin' && $user->isAdmin()) {
                return $next($request);
            }
            if ($role === 'hr_specialist' && $user->isHrSpecialist()) {
                return $next($request);
            }
            if ($role === 'employee' && $user->isEmployee()) {
                return $next($request);
            }
        }

        // Если у пользователя нет ни одной из требуемых ролей,
        // можно перенаправить на предыдущую страницу с ошибкой или на главную
        // Для примера, перенаправим на главную и покажем сообщение (опционально)
        // abort(403, 'Unauthorized action.'); // Или так, если хотите показать стандартную страницу ошибки 403
        return redirect('/')->with('error', 'У вас нет прав для доступа к этой странице.');
    }
}
