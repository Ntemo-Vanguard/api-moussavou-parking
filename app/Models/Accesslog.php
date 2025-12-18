<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Carte;
use App\Models\Parking;

class Accesslog extends Model
{
    protected $table = 'accesslogs';
    public $timestamps = false;
    protected $fillable = ['carte_id', 'parking_id', 'statut', 'raison', 'date_acces'];

    // Un journal d'acces a seulement une carte
    public function carte() { return $this->belongsTo(Carte::class); }

    // Un journal d'acces a seulement un parking
    public function parking() { return $this->belongsTo(Parking::class); }
}