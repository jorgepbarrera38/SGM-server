<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'group_id',
        'date_of_realization',
        'date_of_realization_ending',
        'type_other_maintenance_id',
        'reason',
        'support_description',
        'failure_mode_id',
        'location_id',
        'dependence_id',
        'evidence',
        'official_names',
        'official_position',
        'signature',
        'user_id'
    ];

    public function group () {
        return $this->belongsTo('App\Models\Group');
    }
    
    public function user () {
        return $this->belongsTo('App\Models\User');
    }

    public function device () {
        return $this->belongsTo('App\Models\Device');
    }

    public function typeOtherMaintenance () {
        return $this->belongsTo('App\Models\TypeOtherMaintenance');
    }

    public function replacements () {
        return $this->hasMany('App\Models\OtherMaintenanceReplacement');
    }

    public function photographicAnnexes () {
        return $this->hasMany('App\Models\OtherMaintenancePhotographicAnnexe', 'other_maint_id');
    }

    public function checklists () {
        return $this->hasMany('App\Models\OtherMaintenanceChecklist');
    }

    public function failureMode () {
        return $this->belongsTo('App\Models\FailureMode');
    }

    public function location () {
        return $this->belongsTo('App\Models\Location');
    }

    public function dependence () {
        return $this->belongsTo('App\Models\Dependence');
    }
}
