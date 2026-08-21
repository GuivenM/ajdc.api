<?php
// App\Mail\NotificationNouvelleAdhesion.php

namespace App\Mail;

use App\Models\Adhesion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationNouvelleAdhesion extends Mailable
{
    use Queueable, SerializesModels;

    public $adhesion;

    public function __construct(Adhesion $adhesion)
    {
        $this->adhesion = $adhesion;
    }

    public function build()
    {
        return $this->subject('Nouvelle demande d\'adhésion - AJDCB')
                    ->markdown('emails.nouvelle-adhesion')
                    ->with([
                        'nom' => $this->adhesion->nom,
                        'prenom' => $this->adhesion->prenom,
                        'email' => $this->adhesion->email,
                        'telephone' => $this->adhesion->telephone,
                        'ville' => $this->adhesion->ville,
                        'dashboardUrl' => url('/dashboard/adhesions/' . $this->adhesion->id),
                        'listeUrl' => url('/dashboard/adhesions')
                    ]);
    }
}

