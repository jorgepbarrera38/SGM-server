<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'user_id'
    ];

    public function scheduleMaintenanceDetails () {
        return $this->hasMany('App\Models\ScheduleMaintenanceDetail');
    }

    public function scopeName($query, $name) {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    public function user () {
        return $this->belongsTo('App\Models\User');
    }

    public function recordDelScheduleMaintenances () {
        return $this->hasMany('App\Models\RecordDelScheduleMaintenance');
    }
}
