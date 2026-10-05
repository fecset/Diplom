<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Перенаправляем на страницу личного кабинета
        return redirect()->route('dashboard');
    }
}
