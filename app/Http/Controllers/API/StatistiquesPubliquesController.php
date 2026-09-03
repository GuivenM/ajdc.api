<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Membre;
use App\Models\Partenaire;

class StatistiquesPubliquesController extends Controller
{
    /**
     * Chiffres clés réels affichés sur la page d'accueil (section "stats").
     * Volontairement minimal : uniquement ce qui est réellement mesurable
     * depuis les données existantes.
     */
    public function index()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'membres_actifs' => Membre::actif()->count(),
                    'partenaires_actifs' => Partenaire::actif()->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques publiques',
            ], 500);
        }
    }
}
