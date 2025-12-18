<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Personne;
use App\Models\Carte;
use App\Models\Transaction;
use App\Models\Parking;

class Utilisateur extends Personne
{
    protected static function booted()
    {
        static::addGlobalScope('utilisateur_role', function (Builder $builder) {
            $builder->whereIn('role', ['admin', 'gestionnaire', 'client']);
        });
    }

    // Un utilisateur a une carte
    public function carte() { return $this->hasOne(Carte::class); }

    // Un utilisateur a plusieurs parkings si c'est un gestionnaire
    public function parkings() { return $this->hasMany(Parking::class, 'gestionnaire_id'); }
}