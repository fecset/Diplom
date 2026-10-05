<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_reads', function (Blueprint $t) {
            $t->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->timestamp('read_at');
            $t->primary(['notification_id', 'user_id']);
        });
        DB::table('notifications')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                foreach (json_decode($row->read_by_users ?? '[]', true) ?: [] as $id) {
                    if (DB::table('users')->where('id', $id)->exists()) {
                        DB::table('notification_reads')->insertOrIgnore(['notification_id' => $row->id, 'user_id' => $id, 'read_at' => now()]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // Export receipts back to the legacy format before rollback.
        DB::table('notifications')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('notifications')->where('id', $row->id)->update(['read_by_users' => json_encode(DB::table('notification_reads')->where('notification_id', $row->id)->pluck('user_id')->all())]);
            }
        });
        Schema::dropIfExists('notification_reads');
    }
};
