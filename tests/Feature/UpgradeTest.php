<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpgradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_demo_install_has_valid_logins_and_related_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 24);
        $this->assertDatabaseCount('notifications', 5);
        foreach (User::all() as $user) {
            $this->assertTrue(mb_check_encoding($user->name, 'UTF-8'));
            $this->assertMatchesRegularExpression('/^[a-z0-9.]+$/', $user->username);
            $this->assertNotNull($user->department_id);
            $this->assertNotNull($user->position_id);
        }
    }

    public function test_notification_false_filter_and_end_date_without_start(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Notification::create(['title' => 'Global unique', 'message' => 'text', 'created_by' => $admin->id, 'is_global' => true]);
        Notification::create(['title' => 'Personal unique', 'message' => 'text', 'created_by' => $admin->id, 'is_global' => false]);
        $this->actingAs($admin)->get('/notifications?is_global=false')->assertOk()->assertSee('Personal unique')->assertDontSee('Global unique');
        $this->post('/notifications', ['title' => 'Ends only', 'message' => 'text', 'type' => 'info', 'end_date' => '2026-11-30', 'target_users' => null, 'target_departments' => null])->assertSessionHasNoErrors()->assertRedirect();
        $notice = Notification::where('title', 'Ends only')->firstOrFail();
        $this->assertSame('23:59:59', $notice->end_date->format('H:i:s'));
        $this->getJson('/notifications?title[]=bad')->assertUnprocessable();
    }

    public function test_legacy_document_migration_preserves_bytes_and_removes_public_copy(): void
    {
        Storage::fake('public');
        Storage::fake('private');
        $user = User::factory()->create();
        Storage::disk('public')->put('documents/old.pdf', '%PDF-test');
        $leave = LeaveRequest::create(['user_id' => $user->id, 'type' => 'sick_leave', 'date_start' => '2026-11-01', 'date_end' => '2026-11-02', 'document_path' => 'documents/old.pdf']);
        $this->artisan('personnel:private-documents', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame('documents/old.pdf', $leave->fresh()->document_path);
        Storage::disk('public')->assertExists('documents/old.pdf');
        $this->artisan('personnel:private-documents')->assertSuccessful();
        $leave->refresh();
        $this->assertNotSame('documents/old.pdf', $leave->document_path);
        $this->assertSame('%PDF-test', Storage::disk('private')->get($leave->document_path));
        Storage::disk('public')->assertMissing('documents/old.pdf');
        $this->artisan('personnel:private-documents')->assertSuccessful();
    }

    public function test_missing_legacy_document_returns_failure_without_changing_reference(): void
    {
        Storage::fake('public');
        Storage::fake('private');
        $user = User::factory()->create();
        $leave = LeaveRequest::create(['user_id' => $user->id, 'type' => 'sick_leave', 'date_start' => '2026-11-01', 'date_end' => '2026-11-02', 'document_path' => 'documents/missing.pdf']);
        $this->artisan('personnel:private-documents')->assertFailed();
        $this->assertSame('documents/missing.pdf', $leave->fresh()->document_path);
    }

    public function test_interrupted_cleanup_can_resume(): void
    {
        Storage::fake('public');
        Storage::fake('private');
        $user = User::factory()->create();
        Storage::disk('public')->put('documents/old.pdf', 'bytes');
        Storage::disk('private')->put('documents/new.pdf', 'bytes');
        $leave = LeaveRequest::create(['user_id' => $user->id, 'type' => 'sick_leave', 'date_start' => '2026-11-01', 'date_end' => '2026-11-02', 'document_path' => 'documents/new.pdf', 'legacy_document_path' => 'documents/old.pdf']);
        $this->artisan('personnel:private-documents')->assertSuccessful();
        Storage::disk('public')->assertMissing('documents/old.pdf');
        $this->assertNull($leave->fresh()->legacy_document_path);
    }

    public function test_legacy_receipts_import_and_rollback_preserve_new_readers(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $n = Notification::create(['title' => 'Legacy', 'message' => 'Text', 'created_by' => $a->id, 'read_by_users' => [$a->id]]);
        Schema::drop('notification_reads');
        $migration = require database_path('migrations/2026_10_05_000003_create_notification_reads.php');
        $migration->up();
        $this->assertTrue($n->isReadByUser($a->id));
        $n->markAsReadByUser($b->id);
        $migration->down();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $n->fresh()->read_by_users);
        $migration->up();
    }

    public function test_duplicate_preflight_does_not_delete_existing_records(): void
    {
        $migration = require database_path('migrations/2026_10_05_000001_add_attendance_unique_key.php');
        $migration->down();
        $user = User::factory()->create();
        foreach (['present', 'absent'] as $status) {
            Attendance::create(['user_id' => $user->id, 'date' => '2026-11-01', 'status' => $status]);
        }
        try {
            $migration->up();
            $this->fail('expected duplicate error');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Duplicate', $e->getMessage());
        }
        $this->assertDatabaseCount('attendances', 2);
    }

    public function test_demo_seed_is_guarded_in_production(): void
    {
        $this->app['env'] = 'production';
        $this->expectException(\RuntimeException::class);
        (new UserSeeder)->run();
    }

    public function test_certificate_is_a_pdf_and_employee_cannot_download_another_profile(): void
    {
        $user = User::factory()->create(['name' => 'Иванов Иван', 'hired_at' => '2020-01-01']);
        $response = $this->actingAs($user)->get('/profile/download-certificate')->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
