<?php
// app/Models/PatrolAssignment.php
// app/Models/PatrolAssignment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatrolAssignment extends Model
{
    protected $fillable = ['guard_id', 'patrol_route_id', 'scheduled_start', 'scheduled_end', 'status'];
    protected $casts    = ['scheduled_start' => 'datetime', 'scheduled_end' => 'datetime'];

    // Renombrado para evitar choque con Model::guard()
    public function guardUser()
    {
        return $this->belongsTo(User::class, 'guard_id');
    }

    public function route()
    {
        return $this->belongsTo(PatrolRoute::class, 'patrol_route_id');
    }
    public function scans()
    {
        return $this->hasMany(CheckpointScan::class);
    }
    public function snoozes()
    {
        return $this->hasMany(\App\Models\PatrolAssignmentSnooze::class);
    }
}
