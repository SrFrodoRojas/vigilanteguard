<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessPerson extends Model
{
    protected $table = 'access_people';

    protected $fillable = [
        'access_id',
        'full_name',
        'document',
        'role',
        'is_driver',
        'gender',
        'entry_at',
        'exit_at',
    ];

    protected $casts = [
        // en DB es VARCHAR; lo tratamos como boolean al leer/escribir
        'is_driver' => 'boolean',
        'entry_at'  => 'datetime',
        'exit_at'   => 'datetime',
    ];

    public function access(): BelongsTo
    {
        return $this->belongsTo(Access::class, 'access_id');
    }

    // Enlace por documento (no hay FK en DB; relación "blanda")
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'document', 'document');
    }

    public function scopeActive($q)
    {
        return $q->whereNull('exit_at');
    }
}
