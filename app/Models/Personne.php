<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;

class Personne extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $table = 'personnes';
    protected $fillable = ['nom','email','telephone','mot_de_passe','role','is_blocked'];
    protected $hidden = ['mot_de_passe','remember_token'];

    public function setMotDePasseAttribute($value)
    {
        $this->attributes['mot_de_passe'] = Hash::make($value);
    }

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role
        ];
    }
}