<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordScheduleMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_date',
        'new_date',
        'schedule_maint_detail_id',
        'user_id'
    ];

    public function user () {
        return $this->belongsTo('App\Models\User');
    }
}
