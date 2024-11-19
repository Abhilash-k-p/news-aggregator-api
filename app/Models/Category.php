<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Define the scope to filter out 'unknown' categories
    public function scopeNotUnknown($query)
    {
        return $query->where('name', '!=', 'unknown');
    }

}
