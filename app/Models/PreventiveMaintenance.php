<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_of_realization',
        'evidence',
        'reason',
        'location_id',
        'dependence_id',
        'user_id'
    ];

    public function user () {
        return $this->belongsTo('App\Models\User');
    }

    public function device () {
        return $this->belongsTo('App\Models\Device');
    }

    public function replacementMaintenancables () {
        return $this->morphMany('App\Models\ReplacementMaintenance', 'replacement_maintenancable');
    }

    public function scheduleMaintenanceDetail () {
        return $this->hasOne('App\Models\ScheduleMaintenanceDetail');
    }

    public function location () {
        return $this->belongsTo('App\Models\Location');
    }

    public function dependence () {
        return $this->belongsTo('App\Models\Dependence');
    }
}
