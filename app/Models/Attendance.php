<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    
    /**
     * Атрибуты, которые разрешены для массового присваивания.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'date',
        'status',
        'comment'
    ];
}
