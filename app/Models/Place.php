<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Parking;

class Place extends Model
{
    protected $table = 'places';
    protected $fillable = ['parking_id', 'numero', 'statut', 'code_capteur'];

    // Une place a seulement un parking
    public function parking() { return $this->belongsTo(Parking::class); }
}