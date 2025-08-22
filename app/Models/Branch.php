<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Branch extends Model
{
    protected $table = 'branches';

    protected $fillable = [
        'name',
        'location',
        'color',
        'manager_id',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(Access::class, 'branch_id');
    }
}
