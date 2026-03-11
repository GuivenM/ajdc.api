<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReponseMessage extends Mailable
{
    use Queueable, SerializesModels;

    public Message $messageData;
    public string $reponse;
    public string $objet;

    public function __construct(Message $messageData, string $reponse, string $objet)
    {
        $this->messageData = $messageData;
        $this->reponse = $reponse;
        $this->objet = $objet;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->objet ?: 'Réponse à votre message - AJECB',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reponse-message',
        );
    }
}