<?php

namespace App\Console\Commands;

use App\Mail\CotisationRappel;
use App\Mail\MembreRadieAutomatiquement;
use App\Models\Membre;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class VerifierRetardsCotisations extends Command
{
    /**
     * php artisan cotisations:verifier-retards
     * Prévu pour tourner une fois par mois, après la date limite de paiement
     * (Article 2 : cotisation due au plus tard le 10 de chaque mois) — voir
     * routes/console.php pour la planification.
     */
    protected $signature = 'cotisations:verifier-retards';

    protected $description = "Envoie rappels/avertissements de cotisation et radie automatiquement les membres à 3 mois consécutifs d'impayé (Règlement intérieur, Article 3)";

    public function handle(): int
    {
        $membres = Membre::actif()->get();

        $nbRappels = 0;
        $nbAvertissements = 0;
        $nbRadiations = 0;

        foreach ($membres as $membre) {
            if (!$membre->email) {
                continue;
            }

            $retard = $membre->retardCotisationConsecutif();

            if ($retard >= 3) {
                $membre->update([
                    'statut' => 'inactif',
                    'motif_inactivation' => "Radiation automatique : {$retard} mois consécutifs de cotisation impayée "
                        . "(Règlement intérieur, Article 3), le " . now()->format('d/m/Y'),
                ]);

                $this->envoyerSansBloquer(fn () => Mail::to($membre->email)
                    ->send(new MembreRadieAutomatiquement($membre, $retard)));

                $destinatairesBureau = User::whereIn('role', ['super_admin', 'tresorier'])
                    ->where('est_actif', true)
                    ->pluck('email');
                foreach ($destinatairesBureau as $emailBureau) {
                    $this->envoyerSansBloquer(fn () => Mail::to($emailBureau)
                        ->send(new MembreRadieAutomatiquement($membre, $retard, pourBureau: true)));
                }

                $nbRadiations++;
                $this->line("Radié : {$membre->nom_complet} ({$retard} mois)");
            } elseif ($retard === 2) {
                $this->envoyerSansBloquer(fn () => Mail::to($membre->email)
                    ->send(new CotisationRappel($membre, $retard, avertissementFinal: true)));
                $nbAvertissements++;
            } elseif ($retard === 1) {
                $this->envoyerSansBloquer(fn () => Mail::to($membre->email)
                    ->send(new CotisationRappel($membre, $retard)));
                $nbRappels++;
            }
        }

        $this->info("Terminé : {$nbRappels} rappel(s), {$nbAvertissements} avertissement(s), {$nbRadiations} radiation(s).");

        return self::SUCCESS;
    }

    /**
     * Un email qui échoue (mauvaise config SMTP, etc.) ne doit jamais faire
     * planter le reste de la commande — la radiation elle-même a déjà eu
     * lieu, on ne fait que journaliser l'échec d'envoi.
     */
    private function envoyerSansBloquer(callable $envoi): void
    {
        try {
            $envoi();
        } catch (\Exception $e) {
            \Log::error('cotisations:verifier-retards — échec envoi email : ' . $e->getMessage());
        }
    }
}
