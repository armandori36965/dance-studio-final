<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Location extends Model {
    protected $fillable = ['campus_id', 'name'];
    public function campus() { return $this->belongsTo(Campus::class); }
    public function courses() { return $this->hasMany(Course::class); }
}