<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Сначала удаляем существующее ENUM ограничение
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type VARCHAR(255)");
        
        // Затем добавляем новое ENUM ограничение с дополнительным значением
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type ENUM('vacation', 'sick_leave', 'business_trip')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем к исходному состоянию
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type VARCHAR(255)");
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN type ENUM('vacation', 'sick_leave')");
    }
};
