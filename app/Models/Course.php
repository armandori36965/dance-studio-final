<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CourseTemplate;
use App\Models\Location;
use App\Models\Teacher;
use DateTimeInterface;

class Course extends Model
{
    protected $fillable = ['course_template_id', 'location_id', 'teacher_id', 'start_time', 'end_time', 'capacity'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function courseTemplate()
    {
        return $this->belongsTo(CourseTemplate::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }
}