<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivationCompteAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $lienActivation;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->lienActivation = rtrim(config('app.frontend_url'), '/')
            . '/admin/activer-compte?token=' . $user->activation_token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre accès administrateur AJDCB',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activation-compte-admin',
        );
    }
}
