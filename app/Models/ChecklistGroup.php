<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function checklistItems() {
        return $this->hasMany('App\Models\ChecklistItem');
    }
}
