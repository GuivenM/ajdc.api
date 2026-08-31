<?php

namespace App\Mail;

use App\Models\Membre;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivationCompteMembre extends Mailable
{
    use Queueable, SerializesModels;

    public Membre $membre;
    public string $lienActivation;

    public function __construct(Membre $membre)
    {
        $this->membre = $membre;
        $this->lienActivation = rtrim(config('app.frontend_url'), '/')
            . '/activer-compte?token=' . $membre->activation_token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Activez votre espace membre AJDCB',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activation-compte-membre',
        );
    }
}
