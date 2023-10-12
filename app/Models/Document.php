<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'type_document_id',
        'file',
        'user_id'
    ];

    public function user () {
        return $this->belongsTo('App\Models\User');
    }

    public function typeDocument () {
        return $this->belongsTo('App\Models\TypeDocument');
    }

    public function device () {
        return $this->belongsTo('App\Models\Device');
    }
}
