<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'last_name', 'email', 'password', 'referral_code', 'commission_fixed_value',
        'phone', 'location', 'nif', 'iban', 'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        if (is_null($this->last_name)) {
            return "{$this->name}";
        }

        return "{$this->name} {$this->last_name}";
    }

    /**
     * Get the attributes that should be cast.
     *
     * Nota: a password NÃO tem mutator manual — o cast 'hashed' já encripta
     * automaticamente e não re-encripta um valor que já esteja encriptado
     * (ao contrário de um `bcrypt()` manual, que duplicaria o hash).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'commission_fixed_value' => 'decimal:2',
        ];
    }

    /**
     * Leads (Clients) de que este utilizador é o proprietário/angariador.
     */
    public function ownedLeads()
    {
        return $this->hasMany(Client::class, 'owner_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'aprovado';
    }

    public function isPending(): bool
    {
        return $this->status === 'pendente';
    }

    /**
     * Gera um código de angariador único a partir do nome (ex: "João Silva"
     * → "joaosilva"; se já existir, acrescenta um número: "joaosilva2").
     */
    public static function generateUniqueReferralCode(string $name, ?string $lastName = null): string
    {
        $base = Str::slug(trim($name . ' ' . ($lastName ?? '')), '');
        $base = $base !== '' ? $base : 'angariador';

        $code = $base;
        $suffix = 1;

        while (static::where('referral_code', $code)->exists()) {
            $suffix++;
            $code = $base . $suffix;
        }

        return $code;
    }
}
