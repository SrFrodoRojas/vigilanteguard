<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Checkpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'patrol_route_id',
        'name',
        'latitude',
        'longitude',
        'radius_m',
        'qr_token',
        'short_code',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'radius_m'  => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function (Checkpoint $m) {
            if (empty($m->qr_token)) {
                $m->qr_token = (string) Str::uuid();
            }
            if (empty($m->short_code)) {
                $m->short_code = self::generateShortCode();
            }
        });
    }

    protected static function generateShortCode(): string
    {
        $code = Str::upper(Str::random(8));
        while (self::where('short_code', $code)->exists()) {
            $code = Str::upper(Str::random(8));
        }
        return $code;
    }

    public function route()
    {
        return $this->belongsTo(PatrolRoute::class, 'patrol_route_id');
    }

    public function scans()
    {
        return $this->hasMany(CheckpointScan::class);
    }
}
