<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)], 'phone_number' => 'nullable|string|max:20', 'password' => 'nullable|string|min:8|confirmed', 'current_password' => 'required_with:password|current_password']);
        unset($data['current_password']);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $request->user()->update($data);

        return back()->with('success', 'Профиль обновлён.');
    }

    public function downloadCertificate(Request $request)
    {
        $user = $request->user()->load(['position', 'department']);

        return Pdf::loadView('certificates.work_certificate', compact('user'))->download('spravka_s_mesta_raboty_'.$user->id.'.pdf');
    }
}
