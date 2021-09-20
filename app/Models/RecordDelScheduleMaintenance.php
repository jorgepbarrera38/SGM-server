<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordDelScheduleMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_maintenance_id',
        'device_id',
        'observation',
        'user_id'
    ];

    public function user () {
        return $this->belongsTo('App\Models\User');
    }

    public function device () {
        return $this->belongsTo('App\Models\Device');
    }
}
