<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function scopeName($query, $name) {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    public function checklistGroups() {
        return $this->hasMany('App\Models\ChecklistGroup');
    }

    public function checklistItems() {
        return $this->hasMany('App\Models\ChecklistItem');
    }
}
