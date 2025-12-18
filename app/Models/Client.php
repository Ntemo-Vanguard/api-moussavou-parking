<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Utilisateur;
use App\Models\Carte;
use App\Models\Transaction;

class Client extends Utilisateur
{
    protected $attributes = ['role' => 'client'];
    protected static function booted()
    {
        static::creating(fn($model) => $model->role = 'client');

        static::addGlobalScope('client_role', function (Builder $builder) {
            $builder->where('role', 'client');
        });
    }

    // Un client a seulement une seule carte
    public function carte() { return $this->hasOne(Carte::class, 'utilisateur_id'); }

    // Un client a plusieurs transactions
    public function transactions() { return $this->hasMany(Transaction::class, 'utilisateur_id'); }
}