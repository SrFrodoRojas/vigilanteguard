<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckpointScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'patrol_assignment_id',
        'checkpoint_id',
        'scanned_at',
        'lat',
        'lng',
        'distance_m',
        'accuracy_m',
        'device_info',
        'verified',
        'source_ip',
        'speed_mps', 'jump_m', 'suspect', 'suspect_reason',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'lat'        => 'float',
        'lng'        => 'float',
        'distance_m' => 'integer',
        'accuracy_m' => 'integer',
        'verified'   => 'boolean',
        'speed_mps'  => 'integer', 'jump_m' => 'integer', 'suspect' => 'boolean',

    ];

    public function assignment()
    {
        return $this->belongsTo(PatrolAssignment::class, 'patrol_assignment_id');
    }

    public function checkpoint()
    {
        return $this->belongsTo(Checkpoint::class);
    }

    /**
     * (Opcional) Acceso conveniente: $scan->route retorna la ruta del checkpoint.
     * No es una relación Eloquent, es un accessor.
     */
    public function getRouteAttribute()
    {
        return optional($this->checkpoint)->route;
    }
}
