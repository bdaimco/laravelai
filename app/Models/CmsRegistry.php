<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsRegistry extends Model
{
    protected $fillable = [
        'name',
        'type',
        'slug',
        'structure',
        'ai_provider',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'structure' => 'array',
            'version' => 'integer',
        ];
    }
}