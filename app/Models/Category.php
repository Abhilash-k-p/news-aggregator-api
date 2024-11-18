<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    // Define the scope to filter out 'unknown' categories
    public function scopeNotUnknown($query)
    {
        return $query->where('name', '!=', 'unknown');
    }

}
