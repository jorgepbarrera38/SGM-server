<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'date_of_realization',
        'date_of_realization_ending',
        'reason',
        'support_description',
        'evidence',
        'reason',
        'location_id',
        'dependence_id',
        'official_names',
        'official_position',
        'user_id',
        'signature',
    ];

    public function user () {
        return $this->belongsTo('App\Models\User');
    }

    public function device () {
        return $this->belongsTo('App\Models\Device');
    }

    public function scheduleMaintenanceDetail () {
        return $this->hasOne('App\Models\ScheduleMaintenanceDetail');
    }

    public function replacements () {
        return $this->hasMany('App\Models\PreventiveMaintenanceReplacement', 'prev_maintenance_id');
    }

    public function photographicAnnexes () {
        return $this->hasMany('App\Models\PreventiveMaintenancePhotographicAnnexe', 'prev_m_id');
    }

    public function checklists () {
        return $this->hasMany('App\Models\PreventiveMaintenanceChecklist');
    }

    public function location () {
        return $this->belongsTo('App\Models\Location');
    }

    public function dependence () {
        return $this->belongsTo('App\Models\Dependence');
    }
}
