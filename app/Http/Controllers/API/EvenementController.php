<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EvenementController extends Controller
{
    /**
     * Afficher tous les événements
     */
    public function index(Request $request)
    {
        try {
            $query = Evenement::query();

            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->boolean('a_venir')) {
                $query->aVenir();
            }

            $evenements = $query->orderBy('date_debut', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $evenements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des événements'
            ], 500);
        }
    }

    /**
     * Afficher un événement spécifique
     */
    public function show($id)
    {
        try {
            $evenement = Evenement::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $evenement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Événement non trouvé'
            ], 404);
        }
    }

    /**
     * Créer un nouvel événement
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'contenu' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'heure_debut' => 'nullable|date_format:H:i',
                'heure_fin' => 'nullable|date_format:H:i',
                'lieu' => 'nullable|string|max:255',
                'adresse' => 'nullable|string|max:255',
                'ville' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:100',
                'categorie' => 'nullable|string|max:100',
                'capacite_max' => 'nullable|integer|min:0',
                'prix' => 'nullable|numeric|min:0',
                'devise' => 'nullable|string|max:10',
                'lien_billet' => 'nullable|url',
                'organisateur' => 'nullable|string|max:255',
                'contact_organisateur' => 'nullable|string|max:255',
                'email_contact' => 'nullable|email',
                'telephone_contact' => 'nullable|string|max:30',
                'statut' => 'sometimes|in:publie,brouillon,annule',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('evenements', 'public');
                $data['image'] = str_replace('public/', '', $path);
            }

            $evenement = Evenement::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Événement créé avec succès',
                'data' => $evenement
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un événement
     */
    public function update(Request $request, $id)
    {
        try {
            $evenement = Evenement::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'titre' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'contenu' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
                'date_debut' => 'sometimes|date',
                'date_fin' => 'sometimes|date|after_or_equal:date_debut',
                'heure_debut' => 'nullable|date_format:H:i',
                'heure_fin' => 'nullable|date_format:H:i',
                'lieu' => 'nullable|string|max:255',
                'adresse' => 'nullable|string|max:255',
                'ville' => 'nullable|string|max:255',
                'type' => 'nullable|string|max:100',
                'categorie' => 'nullable|string|max:100',
                'capacite_max' => 'nullable|integer|min:0',
                'prix' => 'nullable|numeric|min:0',
                'devise' => 'nullable|string|max:10',
                'lien_billet' => 'nullable|url',
                'organisateur' => 'nullable|string|max:255',
                'contact_organisateur' => 'nullable|string|max:255',
                'email_contact' => 'nullable|email',
                'telephone_contact' => 'nullable|string|max:30',
                'statut' => 'sometimes|in:publie,brouillon,annule',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('image')) {
                if ($evenement->image) {
                    Storage::delete('public/' . $evenement->image);
                }
                $path = $request->file('image')->store('evenements', 'public');
                $data['image'] = str_replace('public/', '', $path);
            }

            $evenement->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Événement mis à jour avec succès',
                'data' => $evenement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un événement
     */
    public function destroy($id)
    {
        try {
            $evenement = Evenement::findOrFail($id);

            if ($evenement->image) {
                Storage::delete('public/' . $evenement->image);
            }

            $evenement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Événement supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    /**
     * Statistiques des événements
     */
    public function statistiques()
    {
        try {
            $stats = [
                'total' => Evenement::count(),
                'par_statut' => Evenement::selectRaw('statut, count(*) as total')
                    ->groupBy('statut')
                    ->get(),
                'a_venir' => Evenement::aVenir()->count(),
                'prochains_evenements' => Evenement::aVenir()
                    ->orderBy('date_debut')
                    ->limit(5)
                    ->get(['id', 'titre', 'date_debut', 'lieu']),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques'
            ], 500);
        }
    }
}
