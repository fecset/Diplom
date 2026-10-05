<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'read_by_users'
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
        if (!$this->is_active) {
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
        if (!$this->isActive()) {
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
        if ($this->target_departments && in_array($user->department, $this->target_departments)) {
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
    public function isReadByUser($userId)
    {
        $readByUsers = $this->read_by_users ?? [];
        return in_array($userId, $readByUsers);
    }
    
    /**
     * Отметить уведомление как прочитанное пользователем
     */
    public function markAsReadByUser($userId)
    {
        $readByUsers = $this->read_by_users ?? [];
        
        if (!in_array($userId, $readByUsers)) {
            $readByUsers[] = $userId;
            $this->read_by_users = $readByUsers;
            $this->save();
        }
        
        return true;
    }
}
