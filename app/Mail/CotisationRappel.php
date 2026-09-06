<?php

namespace App\Mail;

use App\Models\Membre;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CotisationRappel extends Mailable
{
    use Queueable, SerializesModels;

    public Membre $membre;
    public int $moisRetard;
    public bool $avertissementFinal;

    public function __construct(Membre $membre, int $moisRetard, bool $avertissementFinal = false)
    {
        $this->membre = $membre;
        $this->moisRetard = $moisRetard;
        $this->avertissementFinal = $avertissementFinal;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->avertissementFinal
                ? 'Cotisation AJDCB : dernier rappel avant radiation'
                : 'Rappel : votre cotisation AJDCB du mois',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cotisation-rappel',
        );
    }
}
