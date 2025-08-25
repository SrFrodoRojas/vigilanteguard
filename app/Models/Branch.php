<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\Facades\Schema;

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

    public function users()
    {
        if (Schema::hasColumn('users', 'branch_id')) {
            return $this->hasMany(User::class, 'branch_id');
        }

        if (Schema::hasTable('branch_user')) {
            return $this->belongsToMany(User::class, 'branch_user', 'branch_id', 'user_id');
        }

        // Fallback seguro: relación vacía (0 resultados) para no romper withCount()
        return $this->hasMany(User::class, 'id')->whereRaw('1=0');
    }

    public function accesses(): HasMany
    {
        // Ajustá el nombre del modelo si tu clase no se llama Access
        return $this->hasMany(Access::class, 'branch_id');
    }
}
