<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminRoleMiddleware
{
    /**
     * Проверка на наличие роли администратора. 
     * Ограничивает доступ к функциям редактирования администраторов для HR-специалистов.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        if (!$user->isAdmin()) {
            // Если пользователь не администратор, запрещаем действия с администраторами
            if ($request->route('user') && $request->route('user')->isAdmin()) {
                return redirect()->back()->with('error', 'Только администраторы могут управлять другими администраторами');
            }

            // Запрещаем HR-специалистам создавать администраторов
            if ($request->input('role') === 'admin') {
                return redirect()->back()->with('error', 'Только администраторы могут создавать и редактировать администраторов');
            }
        }

        return $next($request);
    }
} 