<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessOccupant extends Model
{
    protected $fillable = ['access_id','full_name','document'];
    public function access(){ return $this->belongsTo(Access::class); }
}

