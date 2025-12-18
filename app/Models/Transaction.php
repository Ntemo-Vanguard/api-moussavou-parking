<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Carte;
use App\Models\Utilisateur;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $fillable = ['carte_id', 'utilisateur_id', 'montant', 'type', 'moyen', 'statut'];

    // Une transaction a seulement une carte
    public function carte() { return $this->belongsTo(Carte::class); }

    // Une transaction a seulement un utilisateur
    public function utilisateur() { return $this->belongsTo(Utilisateur::class); }
}