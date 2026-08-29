<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use App\Models\Evenement;
use App\Models\Paiement;
use App\Services\FedaPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaiementController extends Controller
{
    /**
     * Initie un paiement FedaPay pour la cotisation mensuelle (Article 2 : 1000 FCFA).
     * Le membre_id est optionnel : à défaut, le paiement est enregistré avec les
     * coordonnées saisies et un admin le rapproche manuellement du bon membre.
     *
     * POST /v1/paiements/cotisation
     */
    public function initierCotisation(Request $request, FedaPayService $fedapay)
    {
        // Rétro-compat : on accepte encore un `mois` unique (string), en plus
        // du nouveau `mois` en tableau pour payer plusieurs mois d'un coup.
        $moisInput = $request->input('mois');
        if (is_string($moisInput)) {
            $request->merge(['mois' => [$moisInput]]);
        }

        $validator = Validator::make($request->all(), [
            'membre_id' => 'nullable|exists:membres,id',
            'mois' => 'required|array|min:1|max:12',
            'mois.*' => 'regex:/^\d{4}-\d{2}$/',
            'nom_payeur' => 'required_without:membre_id|string|max:255',
            'telephone_payeur' => 'required|string|max:30',
            'email_payeur' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $moisListe = array_values(array_unique($data['mois']));
        $montant = CotisationController::MONTANT_DEFAUT * count($moisListe);

        $paiement = Paiement::create([
            'type' => 'cotisation',
            'membre_id' => $data['membre_id'] ?? null,
            'mois' => $moisListe[0],
            'mois_liste' => $moisListe,
            'nom_payeur' => $data['nom_payeur'] ?? null,
            'telephone_payeur' => $data['telephone_payeur'],
            'email_payeur' => $data['email_payeur'] ?? null,
            'montant' => $montant,
            'devise' => 'XOF',
            'statut' => 'en_attente',
        ]);

        $description = count($moisListe) > 1
            ? 'Cotisation AJDCB - ' . count($moisListe) . ' mois (' . implode(', ', $moisListe) . ')'
            : "Cotisation AJDCB - {$moisListe[0]}";

        return $this->demarrerTransaction(
            $fedapay,
            $paiement,
            $description,
            $data['nom_payeur'] ?? null,
            $data['telephone_payeur'],
            $data['email_payeur'] ?? null
        );
    }

    /**
     * Initie un paiement FedaPay pour un billet d'événement payant.
     *
     * POST /v1/paiements/evenements/{id}
     */
    public function initierEvenement(Request $request, $id, FedaPayService $fedapay)
    {
        $evenement = Evenement::findOrFail($id);

        if (!$evenement->prix || $evenement->prix <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cet événement est gratuit, aucun paiement requis.'
            ], 422);
        }

        if ($evenement->est_complet) {
            return response()->json([
                'success' => false,
                'message' => 'Cet événement affiche complet.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'membre_id' => 'nullable|exists:membres,id',
            'nom_payeur' => 'required|string|max:255',
            'telephone_payeur' => 'required|string|max:30',
            'email_payeur' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $paiement = Paiement::create([
            'type' => 'evenement',
            'membre_id' => $data['membre_id'] ?? null,
            'evenement_id' => $evenement->id,
            'nom_payeur' => $data['nom_payeur'],
            'telephone_payeur' => $data['telephone_payeur'],
            'email_payeur' => $data['email_payeur'] ?? null,
            'montant' => $evenement->prix,
            'devise' => $evenement->devise ?: 'XOF',
            'statut' => 'en_attente',
        ]);

        return $this->demarrerTransaction(
            $fedapay,
            $paiement,
            "Billet - {$evenement->titre}",
            $data['nom_payeur'],
            $data['telephone_payeur'],
            $data['email_payeur'] ?? null
        );
    }

    private function demarrerTransaction(
        FedaPayService $fedapay,
        Paiement $paiement,
        string $description,
        ?string $nomPayeur,
        string $telephonePayeur,
        ?string $emailPayeur
    ) {
        try {
            [$prenom, $nom] = $this->splitNom($nomPayeur);

            $customer = array_filter([
                'firstname' => $prenom,
                'lastname' => $nom,
                'email' => $emailPayeur,
                'phone_number' => [
                    'number' => $telephonePayeur,
                    'country' => 'bj',
                ],
            ]);

            $resultat = $fedapay->initierTransaction((float) $paiement->montant, $description, $customer);

            $paiement->update([
                'fedapay_transaction_id' => $resultat['transaction_id'],
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'paiement_id' => $paiement->id,
                    'checkout_url' => $resultat['checkout_url'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur initiation FedaPay', ['erreur' => $e->getMessage(), 'paiement_id' => $paiement->id]);

            $paiement->update(['statut' => 'echoue']);

            return response()->json([
                'success' => false,
                'message' => "Impossible de contacter FedaPay pour l'instant, réessayez dans un instant.",
            ], 502);
        }
    }

    private function splitNom(?string $nomComplet): array
    {
        if (!$nomComplet) {
            return [null, null];
        }
        $parts = preg_split('/\s+/', trim($nomComplet), 2);
        return [$parts[0] ?? null, $parts[1] ?? null];
    }

    /**
     * Webhook FedaPay : notification serveur-à-serveur à chaque changement
     * d'état d'une transaction. Route publique, protégée par la vérification
     * de signature (X-FEDAPAY-SIGNATURE) plutôt que par Sanctum.
     *
     * POST /paiements/webhook
     */
    public function webhook(Request $request, FedaPayService $fedapay)
    {
        $signature = $request->header('X-FEDAPAY-SIGNATURE', '');

        try {
            $event = $fedapay->verifierWebhook($request->getContent(), $signature);
        } catch (\Exception $e) {
            Log::warning('Webhook FedaPay rejeté : signature invalide', ['erreur' => $e->getMessage()]);
            return response()->json(['success' => false], 400);
        }

        $transactionId = $event->entity->id ?? null;
        $paiement = $transactionId ? Paiement::where('fedapay_transaction_id', $transactionId)->first() : null;

        if (!$paiement) {
            // Transaction inconnue de notre système (ou déjà traitée) : on
            // répond 200 quand même pour éviter les tentatives répétées de FedaPay.
            return response()->json(['success' => true]);
        }

        $paiement->fedapay_derniere_reponse = json_encode($event);

        switch ($event->name ?? null) {
            case 'transaction.approved':
                $paiement->statut = 'reussi';
                $paiement->save();
                $this->appliquerPaiementReussi($paiement);
                break;

            case 'transaction.declined':
                $paiement->statut = 'echoue';
                $paiement->save();
                break;

            case 'transaction.canceled':
                $paiement->statut = 'annule';
                $paiement->save();
                break;

            default:
                $paiement->save();
                break;
        }

        return response()->json(['success' => true]);
    }

    /**
     * Répercute un paiement confirmé sur les données métier : marque la
     * cotisation payée, ou incrémente le nombre d'inscrits à l'événement.
     */
    private function appliquerPaiementReussi(Paiement $paiement): void
    {
        if ($paiement->type === 'cotisation' && $paiement->membre_id) {
            $moisListe = $paiement->mois_liste ?: [$paiement->mois];
            $montantParMois = count($moisListe) > 0 ? $paiement->montant / count($moisListe) : $paiement->montant;

            foreach ($moisListe as $mois) {
                Cotisation::updateOrCreate(
                    ['membre_id' => $paiement->membre_id, 'mois' => $mois],
                    [
                        'montant' => $montantParMois,
                        'statut' => 'payee',
                        'date_paiement' => now()->toDateString(),
                        'mode_paiement' => 'mobile_money',
                        'commentaire' => 'Payé en ligne via FedaPay (paiement #' . $paiement->id . ')',
                    ]
                );
            }

            // Premier paiement réussi d'un membre encore en attente : il devient actif.
            \App\Models\Membre::where('id', $paiement->membre_id)
                ->where('statut', 'en_attente_paiement')
                ->update(['statut' => 'actif']);
        }

        if ($paiement->type === 'evenement' && $paiement->evenement_id) {
            Evenement::whereKey($paiement->evenement_id)->increment('nombre_inscrits');
        }
    }
}
