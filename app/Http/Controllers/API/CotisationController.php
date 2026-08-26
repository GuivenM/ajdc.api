<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use App\Models\Membre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CotisationController extends Controller
{
    /**
     * Montant mensuel fixé par le règlement intérieur (Article 2).
     */
    const MONTANT_DEFAUT = 1000;

    /**
     * Liste des cotisations d'un mois pour tous les membres actifs
     * (checklist « à jour / impayé » — combine les membres actifs et
     * les cotisations existantes, même quand aucune ligne n'existe encore).
     *
     * GET /v1/cotisations?mois=2026-08
     */
    public function index(Request $request)
    {
        try {
            $mois = $request->query('mois', now()->format('Y-m'));

            if (!preg_match('/^\d{4}-\d{2}$/', $mois)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format de mois invalide, attendu AAAA-MM'
                ], 422);
            }

            $membres = Membre::actif()->orderBy('nom')->get();
            $cotisations = Cotisation::where('mois', $mois)
                ->get()
                ->keyBy('membre_id');

            $result = $membres->map(function ($membre) use ($cotisations, $mois) {
                $cotisation = $cotisations->get($membre->id);
                return [
                    'membre_id' => $membre->id,
                    'nom_complet' => $membre->nom_complet,
                    'photo_url' => $membre->photo_url,
                    'mois' => $mois,
                    'cotisation_id' => $cotisation->id ?? null,
                    'montant' => $cotisation->montant ?? self::MONTANT_DEFAUT,
                    'statut' => $cotisation->statut ?? 'impayee',
                    'date_paiement' => $cotisation->date_paiement ?? null,
                    'mode_paiement' => $cotisation->mode_paiement ?? null,
                    'commentaire' => $cotisation->commentaire ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des cotisations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marquer une cotisation payée ou impayée pour un membre / un mois donné.
     * Crée la ligne si elle n'existe pas encore (upsert).
     *
     * POST /v1/cotisations/marquer
     */
    public function marquer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'membre_id' => 'required|exists:membres,id',
                'mois' => 'required|regex:/^\d{4}-\d{2}$/',
                'statut' => 'required|in:payee,impayee',
                'montant' => 'nullable|numeric|min:0',
                'date_paiement' => 'nullable|date',
                'mode_paiement' => 'nullable|in:especes,mobile_money,virement,autre',
                'commentaire' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = [
                'montant' => $request->input('montant', self::MONTANT_DEFAUT),
                'statut' => $request->statut,
                'commentaire' => $request->commentaire,
                'enregistre_par' => Auth::id(),
            ];

            if ($request->statut === 'payee') {
                $data['date_paiement'] = $request->input('date_paiement', now()->toDateString());
                $data['mode_paiement'] = $request->input('mode_paiement', 'especes');
            } else {
                $data['date_paiement'] = null;
                $data['mode_paiement'] = null;
            }

            $cotisation = Cotisation::updateOrCreate(
                ['membre_id' => $request->membre_id, 'mois' => $request->mois],
                $data
            );

            // Même règle que côté FedaPay (PaiementController) : un premier
            // paiement confirmé, manuel ou en ligne, active le membre.
            if ($request->statut === 'payee') {
                Membre::where('id', $request->membre_id)
                    ->where('statut', 'en_attente_paiement')
                    ->update(['statut' => 'actif']);
            }

            return response()->json([
                'success' => true,
                'message' => $request->statut === 'payee' ? 'Cotisation marquée payée' : 'Cotisation marquée impayée',
                'data' => $cotisation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de la cotisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques d'un mois : taux à jour, montant collecté, membres en retard.
     *
     * GET /v1/cotisations/statistiques?mois=2026-08
     */
    public function statistiques(Request $request)
    {
        try {
            $mois = $request->query('mois', now()->format('Y-m'));

            $nbMembres = Membre::actif()->count();
            $cotisationsMois = Cotisation::where('mois', $mois)->get();
            $nbPayees = $cotisationsMois->where('statut', 'payee')->count();
            $montantCollecte = $cotisationsMois->where('statut', 'payee')->sum('montant');
            $montantAttendu = $nbMembres * self::MONTANT_DEFAUT;

            return response()->json([
                'success' => true,
                'data' => [
                    'mois' => $mois,
                    'nb_membres' => $nbMembres,
                    'nb_payees' => $nbPayees,
                    'nb_impayees' => $nbMembres - $nbPayees,
                    'taux_a_jour' => $nbMembres > 0 ? round($nbPayees / $nbMembres * 100) : 0,
                    'montant_collecte' => $montantCollecte,
                    'montant_attendu' => $montantAttendu,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Historique des cotisations d'un membre (12 derniers mois) + alerte de
     * radiation si 3 mois consécutifs impayés (Règlement intérieur, Article 3).
     *
     * GET /v1/cotisations/membre/{id}
     */
    public function historiqueMembre($id)
    {
        try {
            $membre = Membre::findOrFail($id);

            $moisAdhesion = $membre->created_at->format('Y-m');

            $mois = collect(range(0, 11))
                ->map(fn($i) => now()->subMonths($i)->format('Y-m'))
                ->values();

            $cotisations = Cotisation::where('membre_id', $id)
                ->whereIn('mois', $mois)
                ->get()
                ->keyBy('mois');

            // Un mois antérieur à l'arrivée du membre n'est ni payé ni impayé :
            // il n'était tout simplement pas encore membre, donc rien à devoir.
            $historique = $mois->map(function ($m) use ($cotisations, $moisAdhesion) {
                if ($m < $moisAdhesion) {
                    return [
                        'mois' => $m,
                        'statut' => 'anterieure_adhesion',
                        'date_paiement' => null,
                        'montant' => null,
                    ];
                }

                $c = $cotisations->get($m);
                return [
                    'mois' => $m,
                    'statut' => $c->statut ?? 'impayee',
                    'date_paiement' => $c->date_paiement ?? null,
                    'montant' => $c->montant ?? null,
                ];
            });

            // Retard consécutif à partir du mois courant (Article 3 : radiation
            // automatique après 3 mois consécutifs de non-paiement). Un mois
            // "anterieure_adhesion" arrête le décompte, comme un mois payé.
            $retardConsecutif = 0;
            foreach ($historique as $entry) {
                if ($entry['statut'] === 'impayee') {
                    $retardConsecutif++;
                } else {
                    break;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'membre' => $membre,
                    'historique' => $historique,
                    'retard_consecutif' => $retardConsecutif,
                    'alerte_radiation' => $retardConsecutif >= 3,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Membre non trouvé'
            ], 404);
        }
    }
}
