<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredentialsUtilisateurMail extends Mailable
{
    use Queueable, SerializesModels;

    public $utilisateur;
    public $motDePasse;

    public function __construct($utilisateur, $motDePasse)
    {
        $this->utilisateur = $utilisateur;
        $this->motDePasse = $motDePasse;
    }

    public function build()
    {
        return $this->subject('Vos accès à la plateforme MOUSSAVOU-PARKING')
                    ->view('emails.credentials_utilisateur');
    }
}