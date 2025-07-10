<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CourseTemplate;
use App\Models\Location;
use App\Models\Teacher;
use App\Models\Campus;
use DateTimeInterface; // 引入

class Course extends Model
{
    protected $fillable = ['course_template_id', 'location_id', 'teacher_id', 'start_time', 'end_time', 'capacity'];
    protected $casts = ['start_time' => 'datetime', 'end_time' => 'datetime'];

    public function courseTemplate(){ return $this->belongsTo(CourseTemplate::class); }
    public function location(){ return $this->belongsTo(Location::class); }
    public function teacher(){ return $this->belongsTo(Teacher::class); }
    public function campus(){ return $this->hasOneThrough(Campus::class, Location::class, 'id', 'id', 'location_id', 'campus_id'); }

    /**
     * 【新增】準備日期以進行序列化，防止時區轉換。
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}