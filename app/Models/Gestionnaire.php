<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Utilisateur;
use App\Models\Parking;

class Gestionnaire extends Utilisateur
{
    protected $attributes = ['role' => 'gestionnaire'];
    protected static function booted()
    {
        static::creating(fn($model) => $model->role = 'gestionnaire');

        static::addGlobalScope('gestionnaire_role', function (Builder $builder) {
            $builder->where('role', 'gestionnaire');
        });
    }

    // Un gestionnaire a plusieurs parking si c'est un gestionnaire
    public function parkings() { return $this->hasMany(Parking::class, 'gestionnaire_id'); }
}