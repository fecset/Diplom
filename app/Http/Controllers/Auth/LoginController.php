<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Для обработки ошибок валидации

class LoginController extends Controller
{
    /**
     * Показать форму входа.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('login'); // Будет создан login.blade.php
    }

    /**
     * Обработать попытку входа.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Пожалуйста, введите логин сотрудника.',
            'password.required' => 'Пожалуйста, введите пароль.',
        ]);

        $credentials = $request->only('username', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'username' => ['Неверный логин или пароль.'],
        ]);
    }

    /**
     * Выход пользователя из системы.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
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
