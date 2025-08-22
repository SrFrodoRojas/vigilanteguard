<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'branch_id', 'phone', 'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // Guarda teléfono sin espacios y caracteres raros
    public function setPhoneAttribute($value)
    {
        $raw    = (string) $value;
        $digits = preg_replace('/\D+/', '', $raw ?? '');
        // Si no viene con código país y parece PY (8-10 dígitos), anteponemos 595
        if ($digits && ! str_starts_with($digits, '595') && strlen($digits) >= 8 && strlen($digits) <= 10) {
            $digits = '595' . $digits;
        }
        $this->attributes['phone'] = $digits ? '+' . $digits : null;
    }

    // Enlace a WhatsApp listo para usar en Blade
    public function getWhatsappUrlAttribute(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone);
        return $digits ? "https://wa.me/{$digits}" : null;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? \Storage::disk('public')->url($this->avatar_path) : null;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class); // Relación con la sucursal
    }

    public function managedBranch()
    {
        return $this->hasOne(Branch::class, 'manager_id');
    }
}
