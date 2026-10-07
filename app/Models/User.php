<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'role', 'referral_code', 'password', 'terms_accepted_at', 'is_demo',
    ];

    protected $hidden = ['password', 'remember_token', 'otp_code'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'otp_sent_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'is_demo' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function business(): HasOne
    {
        return $this->hasOne(Business::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(DataConsent::class);
    }

    public function isVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function firstName(): string
    {
        return explode(' ', trim($this->name))[0];
    }

    public function roleLabel(): string
    {
        return config("tumbuh.roles.{$this->role}", $this->role);
    }
}
