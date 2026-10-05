<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Never silently discard existing personnel records. Resolve duplicates from a backup first.
        if (DB::table('attendances')->select('user_id', 'date')->groupBy('user_id', 'date')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Duplicate attendance records: resolve them before running migrations.');
        }
        Schema::table('attendances', fn (Blueprint $t) => $t->unique(['user_id', 'date']));
    }

    public function down(): void
    {
        Schema::table('attendances', fn (Blueprint $t) => $t->dropUnique(['user_id', 'date']));
    }
};
