<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Department;
use App\Models\Position;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем администратора
        $department = Department::where('name', 'IT-отдел')->first();
        $position = Position::where('name', 'Системный администратор')->first();
        User::create([
            'name' => 'Администратор',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone_number' => '+79990000001',
            'email' => 'admin@company.local',
            'department_id' => $department ? $department->id : null,
            'position_id' => $position ? $position->id : null,
            'hired_at' => '2020-01-15',
        ]);

        // Создаем 3 HR-специалистов
        $hrData = [
            [
                'name' => 'Иванова Анна',
                'username' => 'ivanova',
                'phone_number' => '+79990000002',
                'email' => 'ivanova@company.local',
                'position' => 'HR-специалист',
                'department' => 'Отдел кадров',
                'hired_at' => '2021-03-10',
            ],
            [
                'name' => 'Петров Сергей',
                'username' => 'petrov',
                'phone_number' => '+79990000003',
                'email' => 'petrov@company.local',
                'position' => 'HR-специалист',
                'department' => 'Отдел кадров',
                'hired_at' => '2021-06-15',
            ],
            [
                'name' => 'Смирнова Елена',
                'username' => 'smirnova',
                'phone_number' => '+79990000004',
                'email' => 'smirnova@company.local',
                'position' => 'HR-специалист',
                'department' => 'Отдел кадров',
                'hired_at' => '2022-02-01',
            ],
        ];

        foreach ($hrData as $hr) {
            $department = Department::where('name', $hr['department'])->first();
            $position = Position::where('name', $hr['position'])->first();
            User::create([
                'name' => $hr['name'],
                'username' => $hr['username'],
                'password' => Hash::make('hr12345'),
                'role' => 'hr_specialist',
                'phone_number' => $hr['phone_number'],
                'email' => $hr['email'],
                'department_id' => $department ? $department->id : null,
                'position_id' => $position ? $position->id : null,
                'hired_at' => $hr['hired_at'],
            ]);
        }

        // Создаем 20 сотрудников (используем только существующие отделы и должности из справочников)
        $departments = [
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
            'Юридический отдел'
        ];

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
            'Юрист'
        ];

        $firstNames = ['Александр', 'Алексей', 'Анатолий', 'Андрей', 'Антон', 'Борис', 'Вадим', 'Валентин', 'Валерий', 'Василий', 
                      'Виктор', 'Владимир', 'Геннадий', 'Георгий', 'Дмитрий', 'Евгений', 'Иван', 'Игорь', 'Илья', 'Кирилл',
                      'Анна', 'Алена', 'Екатерина', 'Елена', 'Инна', 'Ирина', 'Марина', 'Мария', 'Наталья', 'Ольга',
                      'Светлана', 'Татьяна', 'Юлия'];
        
        $lastNames = ['Иванов', 'Петров', 'Сидоров', 'Смирнов', 'Кузнецов', 'Соколов', 'Попов', 'Лебедев', 'Козлов', 'Новиков',
                     'Морозов', 'Волков', 'Алексеев', 'Лебедев', 'Семенов', 'Егоров', 'Павлов', 'Козлов', 'Степанов',
                     'Иванова', 'Петрова', 'Сидорова', 'Смирнова', 'Кузнецова', 'Соколова', 'Попова', 'Лебедева', 'Козлова'];
        
        // Даты приема на работу от 2018 до текущего года
        $startDate = Carbon::createFromDate(2018, 1, 1);
        $endDate = Carbon::now();

        for ($i = 1; $i <= 20; $i++) {
            $lastName = $lastNames[array_rand($lastNames)];
            $firstName = $firstNames[array_rand($firstNames)];
            $fullName = $lastName . ' ' . $firstName;
            
            // Для женских имен корректируем фамилию
            if (in_array($firstName, ['Анна', 'Алена', 'Екатерина', 'Елена', 'Инна', 'Ирина', 'Марина', 'Мария', 'Наталья', 'Ольга', 'Светлана', 'Татьяна', 'Юлия'])) {
                $lastName = substr($lastName, 0, -1) . (substr($lastName, -1) === 'в' ? 'ва' : 'а');
            }

            $username = mb_strtolower(transliterate($firstName)) . '.' . mb_strtolower(transliterate($lastName));
            $departmentName = $departments[array_rand($departments)];
            $positionName = $positions[array_rand($positions)];
            $department = Department::where('name', $departmentName)->first();
            $position = Position::where('name', $positionName)->first();

            // Генерируем случайную дату приема в диапазоне
            $daysToAdd = rand(0, $endDate->diffInDays($startDate));
            $hiredAt = $startDate->copy()->addDays($daysToAdd)->format('Y-m-d');
            
            User::create([
                'name' => $fullName,
                'username' => $username . $i,
                'password' => Hash::make('employee123'),
                'role' => 'employee',
                'phone_number' => '+7999' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'email' => $username . $i . '@company.local',
                'department_id' => $department ? $department->id : null,
                'position_id' => $position ? $position->id : null,
                'hired_at' => $hiredAt,
            ]);
        }
    }
}

// Функция для транслитерации кириллицы в латиницу
function transliterate($string) {
    $converter = array(
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
        'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
        'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch',
        'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
    );
    
    return strtr($string, $converter);
}
