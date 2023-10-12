<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherMaintenanceChecklist extends Model
{
    use HasFactory;

    protected $table = 'other_checklists';

    protected $fillable = [
        'name'
    ];

    public function checklistGroups () {
        return $this->hasMany('App\Models\OtherMaintenanceChecklistGroup', 'other_checklist_id');
    }
}