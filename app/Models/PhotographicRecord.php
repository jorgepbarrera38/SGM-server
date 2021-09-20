<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotographicRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'photo',
        'device_id'
    ];

    public function device () {
        return $this->belongsTo('App\Models\Device');
    }
}
