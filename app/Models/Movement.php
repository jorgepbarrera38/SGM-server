<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'dependence_id',
        'responsible_id',
        'observations',
        'user_id'
    ];

    public function location () {
        return $this->belongsTo('App\Models\Location');
    }

    public function dependence () {
        return $this->belongsTo('App\Models\Dependence');
    }

    public function responsible () {
        return $this->belongsTo('App\Models\Responsible');
    }

    public function user () {
        return $this->belongsTo('App\Models\User');
    }
}
