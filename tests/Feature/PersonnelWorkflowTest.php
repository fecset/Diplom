<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\Position;
use App\Models\User;
use App\Services\LeaveWorkflow;
use App\Services\VacationBalance;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PersonnelWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'employee', array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => $role, 'department_id' => Department::firstOrCreate(['name' => 'IT'])->id, 'position_id' => Position::firstOrCreate(['name' => 'Engineer'])->id], $attributes));
    }

    private function leave(User $user, array $attributes = []): LeaveRequest
    {
        return LeaveRequest::create(array_merge(['user_id' => $user->id, 'type' => 'vacation', 'date_start' => '2026-11-01', 'date_end' => '2026-11-03', 'status' => 'new'], $attributes));
    }

    public function test_guest_and_role_boundaries_and_read_only_attendance(): void
    {
        $this->get('/attendance')->assertRedirect('/login');
        $user = $this->user();
        $this->actingAs($user)->get('/attendance')->assertOk()->assertSee('нет отметки', false);
        $this->assertDatabaseCount('attendances', 0);
        $this->get('/hr/personnel')->assertForbidden();
        $this->post('/hr/attendance', ['user_id' => $user->id, 'date' => '2026-11-01', 'status' => 'present'])->assertForbidden();
        $this->getJson('/attendance?date=bad')->assertUnprocessable();
    }

    public function test_personnel_creation_keeps_foreign_keys_and_search_crosses_pages(): void
    {
        $admin = $this->user('admin');
        $department = Department::create(['name' => 'Other']);
        $position = Position::create(['name' => 'Other role']);
        $this->actingAs($admin)->post('/hr/personnel', ['name' => 'Яна Тест', 'username' => 'newemployee', 'department_id' => $department->id, 'position_id' => $position->id, 'role' => 'employee', 'password' => 'Test12345', 'password_confirmation' => 'Test12345'])->assertRedirect();
        $this->assertDatabaseHas('users', ['username' => 'newemployee', 'department_id' => $department->id, 'position_id' => $position->id]);
        User::factory()->count(18)->create(['name' => 'Александр']);
        $this->get('/hr/personnel?name='.urlencode('Яна').'&department='.$department->id)->assertOk()->assertSee('Яна Тест')->assertDontSee('Александр');
        foreach (['department', 'position', 'name', 'role'] as $sort) {
            $this->get('/hr/personnel?sort='.$sort.'&order=invalid')->assertOk();
        }
        $this->actingAs($this->user('hr_specialist'))->post('/hr/personnel', ['role' => 'admin'])->assertForbidden();
    }

    public function test_all_registered_controller_actions_exist(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            $action = $route->getActionName();
            if ($action === 'Closure') {
                continue;
            }
            [$class,$method] = str_contains($action, '@') ? explode('@', $action) : [$action, '__invoke'];
            $this->assertTrue(class_exists($class) && method_exists($class, $method), $action);
        }
    }

    public function test_admin_pages_and_aliases_render(): void
    {
        $admin = $this->user('admin');
        $employee = $this->user();
        $leave = $this->leave($employee, ['type' => 'business_trip', 'destination' => 'Казань', 'purpose' => 'Обучение']);
        $notice = Notification::create(['title' => 'Test', 'message' => 'Text', 'created_by' => $admin->id, 'is_global' => true]);
        $this->actingAs($admin);
        foreach (['/dashboard', '/admin/dashboard', '/hr/personnel', '/admin/personnel', '/admin/users', '/admin/users/create', '/admin/departments', '/admin/departments/create', '/admin/positions', '/admin/positions/create', '/notifications', '/notifications/create', '/notifications/'.$notice->id.'/edit', '/profile', '/admin/analytics', '/attendance', '/hr/leave-requests?department='.$employee->department_id, '/hr/leave-requests/'.$leave->id, '/admin/users/'.$employee->id.'/edit'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/hr/leave-requests/'.$leave->id)->assertSee('Казань')->assertSee('Обучение');
    }

    public function test_leave_validation_preserves_form_and_rejects_overlap(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $this->from('/leave_requests/create')->post('/leave_requests', ['type' => 'business_trip', 'date_start' => '2026-11-01', 'date_end' => '2026-11-02', 'reason' => 'Remember'])->assertSessionHasErrors(['destination', 'purpose'])->assertSessionHasInput('reason', 'Remember');
        $this->leave($user);
        $this->post('/leave_requests', ['type' => 'sick_leave', 'date_start' => '2026-11-02', 'date_end' => '2026-11-04'])->assertSessionHasErrors('date_start');
        $this->assertDatabaseCount('leave_requests', 1);
        $this->postJson('/leave_requests', ['type' => 'vacation', 'date_start' => 'invalid', 'date_end' => 'bad'])->assertUnprocessable();
    }

    public function test_private_uploads_are_unique_and_access_is_checked(): void
    {
        Storage::fake('private');
        $user = $this->user();
        $this->actingAs($user);
        foreach ([1, 10] as $day) {
            $this->post('/leave_requests', ['type' => 'sick_leave', 'date_start' => sprintf('2026-11-%02d', $day), 'date_end' => sprintf('2026-11-%02d', $day), 'document' => UploadedFile::fake()->create('same.pdf', 10, 'application/pdf')])->assertRedirect();
        }
        $leaves = LeaveRequest::get();
        $this->assertCount(2, $leaves);
        $this->assertNotEquals($leaves[0]->document_path, $leaves[1]->document_path);
        Storage::disk('private')->assertExists($leaves[0]->document_path);
        $url = '/leave_requests/'.$leaves[0]->id.'/document';
        $this->get($url)->assertOk();
        $this->actingAs($this->user())->get($url)->assertForbidden();
        $this->actingAs($this->user('hr_specialist'))->get($url)->assertOk();
    }

    public function test_failed_leave_creation_cleans_uploaded_file(): void
    {
        Storage::fake('private');
        $user = $this->user();
        $this->actingAs($user);
        LeaveRequest::creating(fn () => throw new \RuntimeException('forced failure'));
        $this->withoutExceptionHandling();
        try {
            $this->post('/leave_requests', ['type' => 'sick_leave', 'date_start' => '2026-11-01', 'date_end' => '2026-11-01', 'document' => UploadedFile::fake()->create('same.pdf', 10, 'application/pdf')]);
            $this->fail('expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('forced failure', $e->getMessage());
        } finally {
            LeaveRequest::flushEventListeners();
        }
        $this->assertDatabaseCount('leave_requests', 0);
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    public function test_decision_is_atomic_idempotent_and_syncs_attendance(): void
    {
        $admin = $this->user('admin');
        $leave = $this->leave($this->user());
        $this->actingAs($admin);
        $url = '/hr/leave-requests/'.$leave->id;
        $this->put($url, ['status' => 'approved'])->assertRedirect();
        $this->put($url, ['status' => 'approved'])->assertRedirect();
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseCount('attendances', 3);
        $this->assertDatabaseHas('leave_requests', ['id' => $leave->id, 'status' => 'approved', 'decided_by' => $admin->id]);
        $this->put($url, ['status' => 'rejected'])->assertConflict();
        $this->post('/hr/attendance', ['user_id' => $leave->user_id, 'date' => '2026-11-01', 'status' => 'present'])->assertSessionHasErrors('status');
    }

    public function test_notification_failure_rolls_back_decision_and_attendance(): void
    {
        $admin = $this->user('admin');
        $leave = $this->leave($this->user());
        Notification::creating(fn () => throw new \RuntimeException('forced failure'));
        try {
            app(LeaveWorkflow::class)->decide($leave, $admin, 'approved', null);
            $this->fail('expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('forced failure', $e->getMessage());
        } finally {
            Notification::flushEventListeners();
        }
        $this->assertSame('new', $leave->fresh()->status);
        $this->assertDatabaseCount('attendances', 0);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_attendance_duplicate_key_and_approval_conflict(): void
    {
        $admin = $this->user('admin');
        $user = $this->user();
        $leave = $this->leave($user);
        Attendance::create(['user_id' => $user->id, 'date' => '2026-11-01', 'status' => 'present']);
        $this->actingAs($admin)->put('/hr/leave-requests/'.$leave->id, ['status' => 'approved'])->assertSessionHasErrors('status');
        $this->assertSame('new', $leave->fresh()->status);
        $this->expectException(QueryException::class);
        Attendance::create(['user_id' => $user->id, 'date' => '2026-11-01', 'status' => 'absent']);
    }

    public function test_vacation_balance_clips_year_and_merges_intervals(): void
    {
        $user = $this->user();
        $this->leave($user, ['status' => 'approved', 'date_start' => '2025-12-29', 'date_end' => '2026-01-03']);
        $this->leave($user, ['status' => 'approved', 'date_start' => '2026-01-02', 'date_end' => '2026-01-05']);
        $balance = app(VacationBalance::class);
        $this->assertSame(5, $balance->used($user, 2026));
        $this->assertSame(23, $balance->remaining($user, 2026));
        $leave = $this->leave($user, ['date_start' => '2026-11-01', 'date_end' => '2026-11-28']);
        $this->actingAs($this->user('admin'))->put('/hr/leave-requests/'.$leave->id, ['status' => 'approved'])->assertSessionHasErrors('status');
    }

    public function test_notifications_visibility_recipients_receipts_and_payload(): void
    {
        $admin = $this->user('admin');
        $user = $this->user();
        $other = $this->user();
        $notice = Notification::create(['title' => '<img src=x onerror=alert(1)>', 'message' => '<script>alert(2)</script>', 'created_by' => $admin->id, 'is_active' => true, 'target_departments' => [(string) $user->department_id], 'target_users' => [$user->id]]);
        $this->assertTrue($notice->isVisibleToUser($user));
        $response = $this->actingAs($user)->getJson('/api/notifications')->assertOk()->assertJsonPath('unread_count', 1);
        $this->assertEqualsCanonicalizing(['id', 'title', 'message', 'type', 'created_at', 'is_read'], array_keys($response->json('data.0')));
        $this->postJson('/api/notifications/mark-as-read', ['notification_ids' => [$notice->id]])->assertOk();
        $this->actingAs($other)->postJson('/api/notifications/mark-as-read', ['notification_ids' => [$notice->id]])->assertOk();
        $this->assertDatabaseCount('notification_reads', 2);
        $this->postJson('/api/notifications/mark-as-read', ['notification_ids' => [$notice->id]])->assertOk();
        $this->assertDatabaseCount('notification_reads', 2);
        $this->actingAs($admin)->put('/notifications/'.$notice->id, ['title' => 'updated', 'message' => 'text', 'type' => 'info', 'is_active' => 1])->assertRedirect();
        $this->assertSame([$user->id], $notice->fresh()->target_users);
        $this->get('/notifications/'.$notice->id.'/edit')->assertSee('target_users[]', false);
        $this->putJson('/notifications/'.$notice->id, ['title' => 'updated', 'message' => 'text', 'type' => 'info', 'target_departments' => [99999]])->assertUnprocessable();
    }

    public function test_hidden_notification_cannot_be_marked_and_api_paginates(): void
    {
        $admin = $this->user('admin');
        $user = $this->user();
        $hidden = Notification::create(['title' => 'Hidden', 'message' => 'Secret', 'created_by' => $admin->id, 'target_users' => [$admin->id]]);
        foreach (range(1, 25) as $i) {
            Notification::create(['title' => 'Message '.$i, 'message' => 'Visible', 'created_by' => $admin->id, 'is_global' => true]);
        }
        $this->actingAs($user)->getJson('/api/notifications')->assertJsonCount(20, 'data')->assertJsonPath('unread_count', 25);
        $this->postJson('/api/notifications/mark-as-read', ['notification_ids' => [$hidden->id]])->assertOk();
        $this->assertDatabaseCount('notification_reads', 0);
        $this->postJson('/api/notifications/mark-as-read', ['notification_ids' => []])->assertOk();
        $this->assertDatabaseCount('notification_reads', 0);
        $this->postJson('/api/notifications/mark-as-read')->assertOk();
        $this->assertDatabaseCount('notification_reads', 25);
        $this->getJson('/api/notifications')->assertJsonPath('unread_count', 0);
    }

    public function test_login_throttling_and_profile_password_check(): void
    {
        $user = $this->user('employee', ['username' => 'login-test', 'password' => Hash::make('Password123')]);
        foreach (range(1, 5) as $i) {
            $this->post('/login', ['username' => 'login-test', 'password' => 'wrong'])->assertSessionHasErrors('username');
        }
        $this->post('/login', ['username' => 'login-test', 'password' => 'Password123'])->assertSessionHasErrors('username');
        $this->assertGuest();
        $this->actingAs($user)->patch('/profile', ['password' => 'NewPassword123', 'password_confirmation' => 'NewPassword123', 'current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->patch('/profile', ['password' => 'NewPassword123', 'password_confirmation' => 'NewPassword123', 'current_password' => 'Password123'])->assertRedirect();
        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
    }

    public function test_archiving_keeps_history_and_last_admin_cannot_be_demoted(): void
    {
        $admin = $this->user('admin');
        $user = $this->user();
        $leave = $this->leave($user);
        $this->actingAs($admin)->delete('/hr/personnel/'.$user->id)->assertRedirect();
        $this->assertSoftDeleted($user);
        $this->assertSame($user->name, $leave->fresh()->user->name);
        $this->assertDatabaseCount('leave_requests', 1);
        $this->put('/hr/personnel/'.$admin->id, ['name' => $admin->name, 'username' => $admin->username, 'department_id' => $admin->department_id, 'position_id' => $admin->position_id, 'role' => 'employee'])->assertSessionHasErrors('role');
        $this->assertSame('admin', $admin->fresh()->role);
        $this->delete('/hr/personnel/'.$admin->id)->assertRedirect();
        $this->assertNull($admin->fresh()->deleted_at);
    }

    public function test_dictionary_crud_and_reference_guard(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin)->post('/admin/departments', ['name' => 'New department'])->assertRedirect();
        $department = Department::where('name','New department')->firstOrFail();
        $this->put('/admin/departments/'.$department->id,['name' => 'Renamed'])->assertRedirect();
        $this->delete('/admin/departments/'.$admin->department_id)->assertConflict();
        $this->delete('/admin/departments/'.$department->id)->assertRedirect();
        $this->assertDatabaseMissing('departments',['id' => $department->id]);
    }
}
