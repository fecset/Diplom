<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            // Удаляем старые текстовые поля, если есть
            if (Schema::hasColumn('users', 'department')) {
                $table->dropColumn('department');
            }
            if (Schema::hasColumn('users', 'position')) {
                $table->dropColumn('position');
            }
            // Добавляем новые внешние ключи
            $table->unsignedBigInteger('department_id')->nullable()->after('id');
            $table->unsignedBigInteger('position_id')->nullable()->after('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('position_id')->references('id')->on('positions')->nullOnDelete();
        });
    }
    public function down() {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropColumn(['department_id', 'position_id']);
            $table->string('department')->nullable();
            $table->string('position')->nullable();
        });
    }
}; 