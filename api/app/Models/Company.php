<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'uuid',
    'name',
    'trade_name',
    'document',
    'email',
    'phone',
    'website',
    'segment',
    'employees',
    'annual_revenue',
    'description',
    'zipcode',
    'address',
    'number',
    'district',
    'city',
    'state',
    'country',
    'created_by',
])]
class Company extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected function casts(): array
    {
        return [
            'employees' => 'integer',
            'annual_revenue' => 'decimal:2',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
