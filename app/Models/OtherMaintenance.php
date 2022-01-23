<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_of_realization',
        'date_of_realization_ending',
        'type_other_maintenance_id',
        'reason',
        'conclusion',
        'failure_mode_id',
        'location_id',
        'dependence_id',
        'evidence',
        'user_id'
    ];

    public function user () {
        return $this->belongsTo('App\Models\User');
    }

    public function device () {
        return $this->belongsTo('App\Models\Device');
    }

    public function typeOtherMaintenance () {
        return $this->belongsTo('App\Models\TypeOtherMaintenance');
    }

    public function failureMode () {
        return $this->belongsTo('App\Models\FailureMode');
    }

    public function replacementMaintenancables () {
        return $this->morphMany('App\Models\ReplacementMaintenance', 'replacement_maintenancable');
    }

    public function location () {
        return $this->belongsTo('App\Models\Location');
    }

    public function dependence () {
        return $this->belongsTo('App\Models\Dependence');
    }
}
