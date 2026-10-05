<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info'); // info, warning, important
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_global')->default(false); // если true - отображается всем пользователям
            $table->timestamp('start_date')->nullable(); // дата начала отображения
            $table->timestamp('end_date')->nullable(); // дата окончания отображения
            $table->json('target_roles')->nullable(); // JSON массив с ролями, которым показывать уведомление
            $table->json('target_departments')->nullable(); // JSON массив с отделами, которым показывать уведомление
            $table->json('target_users')->nullable(); // JSON массив с ID пользователей, которым показывать уведомление
            $table->json('read_by_users')->nullable(); // JSON массив с ID пользователей, которые прочитали уведомление
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
