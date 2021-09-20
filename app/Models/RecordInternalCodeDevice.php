<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordInternalCodeDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'old_internal_code',
        'new_internal_code',
        'user_id'
    ];
}
