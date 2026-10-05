<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    public function run()
    {
        $positions = [
            'Генеральный директор',
            'Главный инженер',
            'Начальник производства',
            'Мастер участка',
            'Оператор станка',
            'Слесарь',
            'Электромонтер',
            'Инженер-технолог',
            'Инженер по качеству',
            'Кладовщик',
            'Бухгалтер',
            'HR-специалист',
            'Системный администратор',
            'Юрист',
        ];
        foreach ($positions as $name) {
            Position::firstOrCreate(['name' => $name]);
        }
    }
} 