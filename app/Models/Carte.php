<?php

namespace App\Models;

use App\Models\Client;
use App\Models\Accesslog;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Utilisateur;

class Carte extends Model
{
    protected $table = 'cartes';
    protected $fillable = ['utilisateur_id', 'code_rfid', 'solde', 'statut'];

    protected static function boot()
    {
        parent::boot();

        // Générer automatiquement un code RFID lorsqu'une carte est créée
        static::creating(function ($carte) {
            if (empty($carte->code_rfid)) {
                $carte->code_rfid = self::generateRFID();
            }
        });
    }

    // Génère un UID RFID réaliste (ex : "49 1B FB B3")
    public static function generateRFID(): string
    {
        do {
            $code = strtoupper(sprintf(
                "%02X %02X %02X %02X",
                random_int(0, 255),
                random_int(0, 255),
                random_int(0, 255),
                random_int(0, 255)
            ));
        } while (self::where('code_rfid', $code)->exists());

        return $code;
    }

    // Une carte a seulement un utilisateur (client)
    public function utilisateur() { return $this->belongsTo(Utilisateur::class, 'utilisateur_id'); }

    // Une carte a plusieurs transactions
    public function transactions() { return $this->hasMany(Transaction::class); }

    // Une carte a plusieurs journaux
    public function accesslogs() { return $this->hasMany(Accesslog::class); }
}