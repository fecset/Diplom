<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            'Администрация',
            'Производственный отдел',
            'Технический отдел',
            'Отдел главного механика',
            'Отдел главного энергетика',
            'ОТиЗ',
            'ОТК',
            'Склад',
            'Бухгалтерия',
            'Отдел кадров',
            'IT-отдел',
            'Юридический отдел',
        ];
        foreach ($departments as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
} 