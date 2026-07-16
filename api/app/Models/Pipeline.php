<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'color',
    'order',
])]
class Pipeline extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }
}
