<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location', 'manager_id', 'color'];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function accesses()
    {
        return $this->hasMany(Access::class);
    }

    // Scope para búsqueda
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%$search%")
            ->orWhere('location', 'like', "%$search%");
    }

    public function getUiColorAttribute(): string
    {
        if ($this->color) {
            return $this->color;
        }
        // #RRGGBB definido por ti
        // Fallback “bonito” por id (mismo que usabas antes)
        $h = ($this->id * 30) % 360;
        return "hsl({$h}, 70%, 35%)"; // para texto/borde
    }
}
