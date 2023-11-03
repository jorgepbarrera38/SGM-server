<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherMaintenanceReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'other_maintenance_id',
        'replacement_id',
        'quantity',
        'price',
    ];

    public function replacement () {
        return $this->belongsTo('App\Models\Replacement');
    }

    public function otherMaintenance () {
        return $this->belongsTo('App\Models\OtherMaintenance', 'other_maintenance_id');
    }
}
