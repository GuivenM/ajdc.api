<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmationMessage extends Mailable
{
    use Queueable, SerializesModels;

    public Message $messageData;

    public function __construct(Message $messageData)
    {
        $this->messageData = $messageData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de réception de votre message - AJDCB',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmation-message',
        );
    }
}