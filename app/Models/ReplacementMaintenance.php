<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReplacementMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'replacement_id',
        'quantity'
    ];

    public function replacementMaintenancable () {
        return $this->morphTo();
    }

    public function replacement () {
        return $this->belongsTo('App\Models\Replacement');
    }
}
