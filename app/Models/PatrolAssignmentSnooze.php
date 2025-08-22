<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatrolAssignmentSnooze extends Model
{
    protected $fillable = ['patrol_assignment_id','user_id','minutes','reason'];
    public function assignment() { return $this->belongsTo(PatrolAssignment::class,'patrol_assignment_id'); }
    public function user()       { return $this->belongsTo(User::class); }
}
