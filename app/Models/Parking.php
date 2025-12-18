<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Gestionnaire;
use App\Models\Place;

class Parking extends Model
{
    protected $table = 'parkings';
    protected $fillable = ['nom', 'localisation', 'capacite', 'gestionnaire_id'];

    // Un parking a seulement un gestionnaire
    public function gestionnaire() { return $this->belongsTo(Gestionnaire::class, 'gestionnaire_id'); }

    // Un parking a plusieurs places
    public function places() { return $this->hasMany(Place::class); }
}