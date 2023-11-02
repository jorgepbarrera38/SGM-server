<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Replacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
    ];

    public function scopeName($query, $name) {
        return $query->where('name', 'like', '%' . $name . '%');
    }
}
