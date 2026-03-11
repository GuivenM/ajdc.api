<?php

namespace App\Mail;

use App\Models\Adhesion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmationAdhesion extends Mailable
{
    use Queueable, SerializesModels;

    public Adhesion $adhesion;

    public function __construct(Adhesion $adhesion)
    {
        $this->adhesion = $adhesion;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de votre demande d\'adhésion - AJECB',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmation-adhesion',
        );
    }
}