<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'location', 'about_me', 'google2fa_secret', 'google2fa_enabled', 'balance',
    ];

    protected $hidden = [
        'password', 'remember_token', 'google2fa_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Add this method to define the relationship with Transaction
    public function transactions()
    {
        return $this->hasMany(Transaction::class); // Assuming each user has many transactions
    }

    public function setGoogle2faSecretAttribute($value)
    {
        $this->attributes['google2fa_secret'] = $value ? encrypt($value) : null;
    }

    public function getGoogle2faSecretAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }

    //for different roles attached to a user
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // Check if user has a specific role
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

}