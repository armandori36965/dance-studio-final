<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Campus extends Model {
    protected $fillable = ['name', 'color'];
    public function locations() { return $this->hasMany(Location::class); }
}