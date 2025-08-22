<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessPerson extends Model
{
    protected $fillable = [
        'access_id','full_name','document','gender','role','is_driver','entry_at','exit_at'
    ];

    protected $casts = [
        'is_driver' => 'boolean',
        'entry_at'  => 'datetime',
        'exit_at'   => 'datetime',
    ];

    public function access()
    {
        return $this->belongsTo(Access::class);
    }
}
