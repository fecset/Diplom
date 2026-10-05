<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'date_start',
        'date_end',
        'reason',
        'destination',
        'purpose',
        'status',
        'hr_comment',
        'document_path'
    ];
    
    /**
     * Получить сотрудника, которому принадлежит заявка
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
