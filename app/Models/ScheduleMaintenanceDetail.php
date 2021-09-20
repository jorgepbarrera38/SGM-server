<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleMaintenanceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'date'
    ];

    protected $dates = [
        'date'
    ];

    public function device () {
        return $this->belongsTo('App\Models\Device');
    }

    public function preventiveMaintenance () {
        return $this->belongsTo('App\Models\PreventiveMaintenance');
    }

    public function scheduleMaintenance () {
        return $this->belongsTo('App\Models\ScheduleMaintenance');
    }

    public function recordScheduleMaintenances () {
        return $this->hasMany('App\Models\RecordScheduleMaintenance', 'schedule_maint_detail_id');
    }
}
