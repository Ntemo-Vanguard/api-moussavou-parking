<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Carte;

class CarteRechargeNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Carte $carte, public float $montant, public string $moyen) {}

    public function build()
    {
        return $this->subject('Recharge de votre carte RFID')
            ->view('emails.carte_recharge_notification');
    }
}