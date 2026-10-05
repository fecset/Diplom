<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

 // Для обработки ошибок валидации

class LoginController extends Controller
{
    /**
     * Показать форму входа.
     *
     * @return View
     */
    public function showLoginForm()
    {
        return view('login'); // Будет создан login.blade.php
    }

    /**
     * Обработать попытку входа.
     *
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:1024',
        ], [
            'username.required' => 'Пожалуйста, введите логин сотрудника.',
            'password.required' => 'Пожалуйста, введите пароль.',
        ]);

        // Hash prevents usernames/IP addresses appearing verbatim in cache keys.
        $key = 'login:'.hash('sha256', mb_strtolower($request->username).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['username' => 'Слишком много попыток. Повторите через '.RateLimiter::availableIn($key).' сек.']);
        }
        $credentials = $request->only('username', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($key, 60);
        throw ValidationException::withMessages([
            'username' => ['Неверный логин или пароль.'],
        ]);
    }

    /**
     * Выход пользователя из системы.
     *
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/'); // Перенаправляем на главную страницу (которая покажет форму входа)
    }

    /**
     * Создание экземпляра контроллера.
     * Защищаем все методы, кроме showLoginForm, с помощью middleware 'guest',
     * чтобы аутентифицированные пользователи не могли снова зайти на страницу входа.
     * Метод logout защищен middleware 'auth', чтобы только аутентифицированные пользователи могли выйти.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        // $this->middleware('auth')->only('logout'); // 'logout' уже будет доступен только для auth через маршруты
    }
}
