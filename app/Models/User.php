<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable , HasFactory;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // Role checking methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSupport()
    {
        return $this->role === 'support';
    }

    public function isAssistant()
    {
        return $this->role === 'assistant';
    }

    // Permission checking methods
    public function canManageUsers()
    {
        return $this->isAdmin();
    }

    public function canDeleteTickets()
    {
        return $this->isAdmin() || $this->isSupport();
    }

    public function canCreateTickets()
    {
        return true; // All roles can create tickets
    }

    public function canRespondTickets()
    {
        return true; // All roles can respond to tickets
    }

    public function canEditTickets()
    {
        return $this->isAdmin() || $this->isSupport();
    }

    public function canViewAllTickets()
    {
        return $this->isAdmin() || $this->isSupport();
    }

    public function canViewOwnTickets()
    {
        return true; // All roles can view their own tickets
    }
}
