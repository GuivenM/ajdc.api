<?php

namespace App\Mail;

use App\Models\Membre;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembreRadieAutomatiquement extends Mailable
{
    use Queueable, SerializesModels;

    public Membre $membre;
    public int $moisRetard;
    public bool $pourBureau;

    public function __construct(Membre $membre, int $moisRetard, bool $pourBureau = false)
    {
        $this->membre = $membre;
        $this->moisRetard = $moisRetard;
        $this->pourBureau = $pourBureau;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->pourBureau
                ? 'Radiation automatique : ' . $this->membre->prenom . ' ' . $this->membre->nom
                : 'Votre radiation de l\'AJDCB',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.membre-radie',
        );
    }
}
