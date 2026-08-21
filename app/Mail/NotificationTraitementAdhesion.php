<?php

namespace App\Mail;

use App\Models\Adhesion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationTraitementAdhesion extends Mailable
{
    use Queueable, SerializesModels;

    public Adhesion $adhesion;

    public function __construct(Adhesion $adhesion)
    {
        $this->adhesion = $adhesion;
    }

    public function envelope(): Envelope
    {
        $sujet = $this->adhesion->statut === 'approuvee' 
            ? 'Votre adhésion à l\'AJDCB a été approuvée' 
            : 'Statut de votre demande d\'adhésion à l\'AJDCB';

        return new Envelope(
            subject: $sujet,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification-traitement-adhesion',
        );
    }
}