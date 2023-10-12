<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherMaintenanceChecklistGroupItem extends Model
{
    use HasFactory;

    protected $table = 'other_checklist_group_items';
    
    protected $fillable = [
        'description',
        'response',
        'other_checklist_group_id',
    ];
}