<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherMaintenanceChecklistGroup extends Model
{
    use HasFactory;

    protected $table = 'other_checklist_groups';

    protected $fillable = [
        'name',
        'other_checklist_id',
    ];

    public function checklistItems () {
        return $this->hasMany('App\Models\OtherMaintenanceChecklistGroupItem', 'other_checklist_group_id');
    }
}
