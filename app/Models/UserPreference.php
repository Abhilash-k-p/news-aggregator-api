<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{

    use HasFactory;

    protected $fillable = ['user_id', 'preferred_categories', 'preferred_sources', 'preferred_authors'];

    protected function casts(): array
    {
        return [
            'preferred_sources' => 'array',
            'preferred_categories' => 'array',
            'preferred_authors' => 'array',
        ];
    }
}
