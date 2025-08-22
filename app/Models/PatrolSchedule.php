<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatrolSchedule extends Model
{
    protected $fillable = ['patrol_route_id','guard_id','frequency_minutes','active'];

    public function route(): BelongsTo
    {
        return $this->belongsTo(PatrolRoute::class, 'patrol_route_id');
    }

    // Renombrada: antes era guard()
    public function guardUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guard_id');
    }
}
