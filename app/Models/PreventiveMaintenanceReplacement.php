<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenanceReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'prev_maintenance_id',
        'replacement_id',
        'quantity',
    ];

    public function replacement () {
        return $this->belongsTo('App\Models\Replacement');
    }

    public function preventiveMaintenance () {
        return $this->belongsTo('App\Models\PreventiveMaintenance', 'prev_maintenance_id');
    }
}
