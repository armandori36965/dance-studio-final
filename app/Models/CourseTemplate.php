<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CourseTemplate extends Model {
    protected $fillable = ['name', 'price']; // 移除 color
    public function courses() { return $this->hasMany(Course::class); }
}