<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $table = 'people';

    protected $fillable = [
        'full_name',
        'document',
        'gender',
    ];

    // Si no quieres timestamps automáticos, descomenta:
    // public $timestamps = false;

    public function accessPeople(): HasMany
    {
        // Relación por texto (document)
        return $this->hasMany(AccessPerson::class, 'document', 'document');
    }
}
