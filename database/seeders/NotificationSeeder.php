<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Department;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Сначала находим админа для создания уведомлений от его имени
        $admin = User::where('role', 'admin')->first();
        
        if (!$admin) {
            $this->command->info('Администратор не найден. Создайте администратора перед запуском этого сидера.');
            return;
        }
        
        // Создаем глобальное важное уведомление
        Notification::create([
            'title' => 'Важное корпоративное собрание',
            'message' => 'Приглашаем всех сотрудников на ежемесячное собрание, которое состоится 25 числа в 14:00 в конференц-зале.',
            'type' => 'important',
            'created_by' => $admin->id,
            'is_global' => true,
            'is_active' => true,
        ]);
        
        // Создаем уведомление только для HR-специалистов
        Notification::create([
            'title' => 'Обновление системы учета рабочего времени',
            'message' => 'Уважаемые HR-специалисты, сообщаем, что с 1 числа следующего месяца вступают в силу изменения в системе учета рабочего времени. Пожалуйста, ознакомьтесь с обновленными правилами.',
            'type' => 'info',
            'created_by' => $admin->id,
            'is_global' => false,
            'target_roles' => ['hr_specialist'],
            'is_active' => true,
        ]);
        
        // Создаем уведомление с датой начала и окончания
        Notification::create([
            'title' => 'Плановые технические работы',
            'message' => 'Информируем о проведении плановых технических работ с 20 по 22 число текущего месяца. В это время возможны кратковременные перебои в работе системы.',
            'type' => 'warning',
            'created_by' => $admin->id,
            'is_global' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
            'is_active' => true,
        ]);
        
        // Создаем уведомление для определенного отдела
        $itDepartment = Department::where('name', 'ИТ-отдел')->first();
        
        if ($itDepartment) {
            Notification::create([
                'title' => 'Обновление ПО',
                'message' => 'Уважаемые сотрудники ИТ-отдела, просим установить обновление антивирусного ПО на все рабочие станции до конца недели.',
                'type' => 'info',
                'created_by' => $admin->id,
                'is_global' => false,
                'target_departments' => [$itDepartment->id],
                'is_active' => true,
            ]);
        }
        
        // Создаем неактивное уведомление
        Notification::create([
            'title' => 'Тестовое уведомление',
            'message' => 'Это тестовое уведомление, которое не должно отображаться пользователям.',
            'type' => 'info',
            'created_by' => $admin->id,
            'is_global' => true,
            'is_active' => false,
        ]);
        
        $this->command->info('Тестовые уведомления успешно созданы.');
    }
}
