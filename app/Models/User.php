<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable , HasFactory;

    protected $fillable = ['nome', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSuporte()
    {
        return $this->role === 'suporte';
    }

    public function isAssistente()
    {
        return $this->role === 'assistente';
    }
}
