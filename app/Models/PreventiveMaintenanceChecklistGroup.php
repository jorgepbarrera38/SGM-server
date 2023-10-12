<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenanceChecklistGroup extends Model
{
    use HasFactory;

    protected $table = 'preventive_checklist_groups';

    protected $fillable = [
        'name',
        'prev_checklist_id',
    ];

    public function checklistItems () {
        return $this->hasMany('App\Models\PreventiveMaintenanceChecklistGroupItem', 'prev_checklist_group_id');
    }
}
