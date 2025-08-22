<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    protected $fillable = [
        'branch_id',
        'type',
        'plate',
        'marca_vehiculo',
        'color_vehiculo',
        'tipo_vehiculo',
        'full_name',
        'document',
        'entry_at',
        'entry_note',
        'vehicle_exit_at',
        'exit_at',
        'exit_note',
        'user_id',
        'vehicle_exit_driver_id',
    ];
    protected $casts = [
        'entry_at'        => 'datetime',
        'exit_at'         => 'datetime',
        'vehicle_exit_at' => 'datetime',
    ];

    public function people()
    {
        return $this->hasMany(\App\Models\AccessPerson::class);
    }

    public function occupantsInside()
    {
        return $this->people()->whereNull('exit_at');
    }

    public function getInsideCountAttribute(): int
    {
        return $this->occupantsInside()->count();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
/*     public function occupants()
    {
        return $this->hasMany(AccessOccupant::class);
    } */

    // Scope de registros activos (sin salida)
    public function scopeActive($q)
    {
        return $q->whereNull('exit_at');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
