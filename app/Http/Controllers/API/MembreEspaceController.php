<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Services\ImageCompressionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

// Endpoints self-service de l'espace membre : contrairement à
// MembreController (admin, gère tous les membres), tout ici agit
// exclusivement sur $request->user() — un membre ne peut jamais lire ou
// modifier la fiche d'un autre membre via ces routes.
class MembreEspaceController extends Controller
{
    /**
     * Mise à jour du profil du membre connecté. Volontairement restreint
     * aux champs "personnels" : poste/commission/statut restent gérés par
     * l'admin (voir MembreController::update côté espace admin).
     *
     * PUT /v1/membre/profil
     */
    public function updateProfil(Request $request, ImageCompressionService $compressor)
    {
        try {
            $membre = $request->user();

            $validator = Validator::make($request->all(), [
                'whatsapp' => 'nullable|string|max:30',
                'facebook' => 'nullable|string|max:255',
                'instagram' => 'nullable|string|max:255',
                'linkedin' => 'nullable|string|max:255',
                'twitter' => 'nullable|string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('photo')) {
                if ($membre->photo) {
                    Storage::disk('public')->delete($membre->photo);
                }
                $data['photo'] = $compressor->store($request->file('photo'), 'membres');
            }

            $membre->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour',
                'data' => $membre->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Événements à venir, avec le statut d'inscription du membre connecté
     * sur chacun ('inscrit' | 'confirme' | null si pas encore inscrit).
     *
     * GET /v1/membre/evenements
     */
    public function evenements(Request $request)
    {
        try {
            $membre = $request->user();

            $evenements = Evenement::aVenir()
                ->orderBy('date_debut')
                ->get()
                ->map(function ($evenement) use ($membre) {
                    $participation = $evenement->participants
                        ->firstWhere('id', $membre->id);

                    $array = $evenement->toArray();
                    $array['mon_inscription'] = $participation
                        ? $participation->pivot->statut
                        : null;

                    return $array;
                });

            return response()->json([
                'success' => true,
                'data' => $evenements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des événements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Inscription à un événement gratuit. Les événements payants passent
     * par PaiementController::initierEvenement — la place n'est confirmée
     * qu'après paiement réussi (webhook FedaPay), pas ici.
     *
     * POST /v1/membre/evenements/{id}/inscription
     */
    public function inscrire(Request $request, $id)
    {
        try {
            $membre = $request->user();
            $evenement = Evenement::findOrFail($id);

            if ($evenement->prix > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet événement est payant : passez par le paiement pour réserver votre place.'
                ], 422);
            }

            if ($evenement->est_complet && !$evenement->participants->contains($membre->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet événement affiche complet.'
                ], 422);
            }

            $dejaInscrit = $evenement->participants->contains($membre->id);

            $evenement->participants()->syncWithoutDetaching([
                $membre->id => [
                    'statut' => 'inscrit',
                    'date_inscription' => now(),
                ]
            ]);

            if (!$dejaInscrit) {
                $evenement->increment('nombre_inscrits');
            }

            return response()->json([
                'success' => true,
                'message' => 'Inscription confirmée'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Annulation d'une inscription à un événement gratuit.
     *
     * DELETE /v1/membre/evenements/{id}/inscription
     */
    public function desinscrire(Request $request, $id)
    {
        try {
            $membre = $request->user();
            $evenement = Evenement::findOrFail($id);

            $etaitInscrit = $evenement->participants->contains($membre->id);

            $evenement->participants()->detach($membre->id);

            if ($etaitInscrit && $evenement->nombre_inscrits > 0) {
                $evenement->decrement('nombre_inscrits');
            }

            return response()->json([
                'success' => true,
                'message' => 'Inscription annulée'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
