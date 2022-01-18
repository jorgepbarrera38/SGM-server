<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'resume_number',
        'group_id',
        'element_id',
        'location_id',
        'dependence_id',
        'responsible_id',
        'internal_code',
        'serial_number',
        'brand_id',
        'model',
        'description',
        'photo',
        'observations',
        'start_operation',
        'date_end_guarantee',
        'useful_life',
        'description_support_preventive_maintenance',
        'active',
        'down_date',
        'down_reason',
        'down_evidence',
        'down_user_id'
    ];

    public function group () {
        return $this->belongsTo('App\Models\Group');
    }

    public function element () {
        return $this->belongsTo('App\Models\Element');
    }

    public function dependence () {
        return $this->belongsTo('App\Models\Dependence');
    }

    public function responsible () {
        return $this->belongsTo('App\Models\Responsible');
    }

    public function brand () {
        return $this->belongsTo('App\Models\Brand');
    }

    public function location () {
        return $this->belongsTo('App\Models\Location');
    }

    public function movements () {
        return $this->hasMany('App\Models\Movement');
    }

    public function preventiveMaintenances () {
        return $this->hasMany('App\Models\PreventiveMaintenance');
    }

    public function otherMaintenances () {
        return $this->hasMany('App\Models\OtherMaintenance');
    }

    public function downUser () {
        return $this->belongsTo('App\Models\User', 'down_user_id');
    }

    public function recordInternalCodeDevices () {
        return $this->hasMany('App\Models\RecordInternalCodeDevice');
    }

    public function documents () {
        return $this->hasMany('App\Models\Document');
    }

    public function photographicRecords () {
        return $this->hasMany('App\Models\PhotographicRecord');
    }

    public function scheduleMaintenanceDetails () {
        return $this->hasMany('App\Models\ScheduleMaintenanceDetail');
    }

    public function scopeInternalCodeOrSerial ($query, $internalCodeOrSerial) {
        return $query->where('internal_code', 'like', '%'. $internalCodeOrSerial . '%')->orWhere('serial_number', 'like', '%'. $internalCodeOrSerial . '%');
    }

    public function scopeGroup ($query, $group) {
        if ($group) {
            return $query->where('group_id', $group);
        }
    }

    public function scopeElement ($query, $element) {
        if ($element) {
            return $query->where('element_id', $element);
        }
    }

    public function scopeLocation ($query, $location) {
        if ($location) {
            return $query->where('location_id', $location);
        }
    }

    
    public function scopeDependence ($query, $dependence) {
        if ($dependence) {
            return $query->where('dependence_id', $dependence);
        }
    }

    public function scopeResponsible ($query, $responsible) {
        if ($responsible) {
            return $query->where('responsible_id', $responsible);
        }
    }
}
