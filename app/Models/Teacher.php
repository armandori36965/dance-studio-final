<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    /**
     * 【修改】將 'rates' 加入到 $fillable 陣列中
     */
    protected $fillable = ['name', 'phone_number', 'rates'];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}