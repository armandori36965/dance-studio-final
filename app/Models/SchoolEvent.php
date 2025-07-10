<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SchoolEvent extends Model {
    protected $fillable = ['location_id', 'title', 'start_date', 'end_date'];
    public function location() { return $this->belongsTo(Location::class); }
}