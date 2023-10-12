<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'checklist_group_id',
    ];

    public function checklistGroup() {
        return $this->belongsTo('App\Models\ChecklistGroup');
    }
}
