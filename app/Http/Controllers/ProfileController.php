<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // ... existing code ...
    }

    /**
     * Download certificate.
     */
    public function downloadCertificate(Request $request)
    {
        $user = $request->user();
        
        // Загружаем связанные данные пользователя (должность и отдел)
        $user->load(['position', 'department']);

        // Добавляем отладочную информацию
        Log::info('User data for certificate:', [
            'user_id' => $user->id,
            'name' => $user->name,
            'full_name' => $user->full_name ?? 'N/A', // Проверяем full_name
            'position' => $user->position?->name,
            'department' => $user->department?->name,
        ]);

        // Генерируем PDF из представления
        $pdf = Pdf::loadView('certificates.work_certificate', compact('user'));

        // Возвращаем PDF для скачивания
        return $pdf->download('spravka_s_mesta_raboty_' . $user->id . '.pdf');
    }
}
