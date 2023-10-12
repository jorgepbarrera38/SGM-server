<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenanceChecklist extends Model
{
    use HasFactory;

    protected $table = 'preventive_checklists';

    protected $fillable = [
        'name'
    ];

    public function checklistGroups () {
        return $this->hasMany('App\Models\PreventiveMaintenanceChecklistGroup', 'prev_checklist_id');
    }
}