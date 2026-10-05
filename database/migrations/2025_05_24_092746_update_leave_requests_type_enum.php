<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', fn (Blueprint $table) => $table->enum('type', ['vacation', 'sick_leave', 'business_trip'])->change());
    }

    public function down(): void
    {
        if (DB::table('leave_requests')->where('type', 'business_trip')->exists()) {
            throw new RuntimeException('Cannot roll back while business trips exist.');
        }
        Schema::table('leave_requests', fn (Blueprint $table) => $table->enum('type', ['vacation', 'sick_leave'])->change());
    }
};
