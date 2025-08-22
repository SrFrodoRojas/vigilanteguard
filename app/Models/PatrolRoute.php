<?php
// app/Models/PatrolRoute.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatrolRoute extends Model
{
    protected $fillable = ['branch_id','name','expected_duration_min','active'];
    public function branch(){ return $this->belongsTo(Branch::class); }
    public function checkpoints(){ return $this->hasMany(Checkpoint::class); }
    public function assignments(){ return $this->hasMany(PatrolAssignment::class); }
}
