<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->softDeletes();
            $t->unsignedSmallInteger('vacation_days_per_year')->default(28);
        });
        Schema::table('leave_requests', function (Blueprint $t) {
            $t->string('legacy_document_path')->nullable();
            $t->foreignId('decided_by')->nullable()->constrained('users');
            $t->timestamp('decided_at')->nullable();
            $t->index(['user_id', 'status', 'date_start', 'date_end']);
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $t) {
            $t->dropForeign(['decided_by']);
            $t->dropIndex(['user_id', 'status', 'date_start', 'date_end']);
            $t->dropColumn(['decided_by', 'decided_at', 'legacy_document_path']);
        });
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['deleted_at', 'vacation_days_per_year']));
    }
};
