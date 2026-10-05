<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'personnel:create-admin {username} {--name=Администратор}';

    protected $description = 'Create an administrator using an interactive password (never a command-line argument).';

    public function handle(): int
    {
        $password = $this->secret('Пароль (минимум 12 символов)');
        $confirmation = $this->secret('Повторите пароль');
        $data = ['username' => $this->argument('username'), 'name' => $this->option('name'), 'password' => $password, 'password_confirmation' => $confirmation];
        $validator = Validator::make($data, ['username' => 'required|string|max:50|unique:users', 'name' => 'required|string|max:255', 'password' => 'required|string|min:12|confirmed']);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

return self::FAILURE;
        }
        User::create(['username' => $data['username'], 'name' => $data['name'], 'password' => $password, 'role' => 'admin']);
        $this->info('Администратор создан. Отдел и должность можно заполнить в кадровом учёте.');

        return self::SUCCESS;
    }
}
