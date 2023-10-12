<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenanceChecklistGroupItem extends Model
{
    use HasFactory;

    protected $table = 'preventive_checklist_group_items';
    
    protected $fillable = [
        'description',
        'response',
        'prev_checklist_group_id',
    ];
}