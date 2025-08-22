<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Access extends Model
{
    protected $table = 'accesses';

    protected $fillable = [
        'type',
        'plate',
        'marca_vehiculo',
        'color_vehiculo',
        'tipo_vehiculo',
        'people_count',           // denormalizado (derivado)
        'full_name',              // denormalizado (derivado)
        'document',               // denormalizado (derivado)
        'entry_at',
        'entry_note',
        'vehicle_exit_at',
        'exit_at',
        'exit_note',
        'user_id',
        'branch_id',
        'vehicle_exit_driver_id', // apunta a access_people.id (sin FK en DB)
    ];

    protected $casts = [
        'entry_at'        => 'datetime',
        'exit_at'         => 'datetime',
        'vehicle_exit_at' => 'datetime',
        // si people_count viene como texto numérico, lo convertimos a int al leer:
        'people_count'    => 'integer',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Ocupantes canónicos (tabla access_people)
    public function people(): HasMany
    {
        return $this->hasMany(AccessPerson::class, 'access_id');
    }

    // Conductor de salida (referencia blanda a access_people.id)
    public function exitDriver(): BelongsTo
    {
        return $this->belongsTo(AccessPerson::class, 'vehicle_exit_driver_id');
    }

    // Scopes útiles
    public function scopeActive($q)
    {
        return $q->whereNull('exit_at');
    }

    // Atributo calculado
    protected $appends = ['is_active'];

    public function getIsActiveAttribute(): bool
    {
        return $this->exit_at === null;
    }
}
