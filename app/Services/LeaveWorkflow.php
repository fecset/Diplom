<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveWorkflow
{
    public function checkOverlap(int $userId, string $start, string $end, ?int $except = null, array $statuses = ['new', 'approved']): void
    {
        if (LeaveRequest::where('user_id', $userId)->whereIn('status', $statuses)
            ->when($except, fn ($q) => $q->where('id', '!=', $except))->where('date_start', '<=', $end)->where('date_end', '>=', $start)->exists()) {
            throw ValidationException::withMessages(['date_start' => 'Период пересекается с другой заявкой сотрудника.']);
        }
    }

    public function decide(LeaveRequest $request, User $actor, string $status, ?string $comment): void
    {
        abort_unless($actor->isAdmin() || $actor->isHrSpecialist(), 403);
        abort_unless(in_array($status, ['approved', 'rejected'], true), 422);
        abort_if($request->user_id === $actor->id && ! $actor->isAdmin(), 403);
        DB::transaction(function () use ($request, $actor, $status, $comment) {
            // All attendance writers and leave writers lock the employee first.
            $user = User::withTrashed()->whereKey($request->user_id)->lockForUpdate()->firstOrFail();
            $leave = LeaveRequest::whereKey($request->id)->lockForUpdate()->firstOrFail();
            if ($leave->status === $status) {
                return;
            } // Repeat submission is idempotent.
            abort_unless($leave->status === 'new', 409, 'Решение по заявке уже принято.');
            if ($status === 'approved') {
                $this->checkOverlap($user->id, $leave->date_start, $leave->date_end, $leave->id, ['approved']);
                if ($leave->type === 'vacation') {
                    $balance = app(VacationBalance::class);
                    $from = CarbonImmutable::parse($leave->date_start);
                    $to = CarbonImmutable::parse($leave->date_end);
                    foreach (range($from->year, $to->year) as $year) {
                        $a = $from->max(CarbonImmutable::create($year, 1, 1));
                        $b = $to->min(CarbonImmutable::create($year, 12, 31));
                        $needed = (int) $a->diffInDays($b) + 1;
                        if ($balance->used($user, $year) + $needed > $user->vacation_days_per_year) {
                            throw ValidationException::withMessages(['status' => "Недостаточно дней отпуска за {$year} год."]);
                        }
                    }
                }
                // Trips count as present in the current four-status attendance policy.
                $attendanceStatus = match ($leave->type) {
                    'vacation' => 'vacation','sick_leave' => 'sick_leave',default => 'present'
                };
                $existing = Attendance::where('user_id', $user->id)->whereBetween('date', [$leave->date_start, $leave->date_end])->get();
                if ($existing->contains(fn ($row) => $row->status !== $attendanceStatus)) {
                    throw ValidationException::withMessages(['status' => 'Табель содержит противоречащие отметки. Исправьте их перед одобрением.']);
                }
                for ($day = CarbonImmutable::parse($leave->date_start); $day->lte(CarbonImmutable::parse($leave->date_end)); $day = $day->addDay()) {
                    Attendance::firstOrCreate(['user_id' => $user->id, 'date' => $day->toDateString()], ['status' => $attendanceStatus, 'comment' => 'Заявка #'.$leave->id]);
                }
            }
            $leave->update(['status' => $status, 'hr_comment' => $comment, 'decided_by' => $actor->id, 'decided_at' => now()]);
            Notification::create(['title' => 'Обновление статуса заявки', 'message' => 'Заявка #'.$leave->id.' за '.$leave->date_start.' '.($status === 'approved' ? 'одобрена' : 'отклонена').'. '.($comment ?? ''),
                'type' => $status === 'approved' ? 'info' : 'warning', 'created_by' => $actor->id, 'is_global' => false, 'is_active' => true, 'target_users' => [$user->id]]);
        }, 3);
    }
}
