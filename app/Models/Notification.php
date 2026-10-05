<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'created_by',
        'is_global',
        'start_date',
        'end_date',
        'target_roles',
        'target_departments',
        'target_users',
        'is_active',
        'read_by_users',
    ];

    protected $casts = [
        'is_global' => 'boolean',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'target_roles' => 'array',
        'target_departments' => 'array',
        'target_users' => 'array',
        'read_by_users' => 'array',
    ];

    /**
     * Получить пользователя, создавшего уведомление
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Проверить, является ли уведомление активным на данный момент
     */
    public function isActive()
    {
        if (! $this->is_active) {
            return false;
        }

        $now = Carbon::now();

        // Проверка даты начала отображения
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        // Проверка даты окончания отображения
        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Проверить, должно ли уведомление отображаться для пользователя
     */
    public function isVisibleToUser(User $user)
    {
        // Если уведомление не активно, не показываем его
        if (! $this->isActive()) {
            return false;
        }

        // Если уведомление глобальное, показываем его всем
        if ($this->is_global) {
            return true;
        }

        // Проверяем по роли
        if ($this->target_roles && in_array($user->role, $this->target_roles)) {
            return true;
        }

        // Проверяем по отделу
        if ($this->target_departments && in_array($user->department_id, $this->target_departments)) {
            return true;
        }

        // Проверяем по ID пользователя
        if ($this->target_users && in_array($user->id, $this->target_users)) {
            return true;
        }

        return false;
    }

    /**
     * Проверить, прочитано ли уведомление пользователем
     */
    public function reads()
    {
        return $this->belongsToMany(User::class, 'notification_reads')->withPivot('read_at');
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->where(function ($q) use ($user) {
                $q->where('is_global', true)->orWhereJsonContains('target_roles', $user->role)
                    ->orWhereJsonContains('target_users', $user->id)->orWhereJsonContains('target_users', (string) $user->id);
                if ($user->department_id) {
                    $q->orWhereJsonContains('target_departments', $user->department_id)->orWhereJsonContains('target_departments', (string) $user->department_id);
                }
            });
    }

    public function isReadByUser($userId)
    {
        return $this->relationLoaded('reads') ? $this->reads->contains('id', $userId) : $this->reads()->where('users.id', $userId)->exists();
    }

    public function markAsReadByUser($userId)
    {
        DB::table('notification_reads')->insertOrIgnore(['notification_id' => $this->id, 'user_id' => $userId, 'read_at' => now()]);

        return true;
    }
}
