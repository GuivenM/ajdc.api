<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Actualite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ActualiteController extends Controller
{
    /**
     * Afficher toutes les actualités
     */
    public function index()
    {
        try {
            $actualites = Actualite::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $actualites,
                'message' => 'Actualités récupérées avec succès'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des actualités',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher les actualités par type
     */
    public function getByType($type)
    {
        try {
            $actualites = Actualite::where('type', $type)
                ->where('statut', 'publie')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $actualites,
                'message' => 'Actualités récupérées avec succès'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des actualités',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher les 3 dernières actualités
     */
    public function dernieresActualites()
    {
        try {
            $actualites = Actualite::where('statut', 'publie')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $actualites,
                'message' => 'Dernières actualités récupérées avec succès'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des actualités',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une actualité spécifique
     */
    public function show($id)
    {
        try {
            $actualite = Actualite::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $actualite,
                'message' => 'Actualité récupérée avec succès'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Actualité non trouvée'
            ], 404);
        }
    }

    /**
     * Créer une nouvelle actualité
     */
    public function store(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'contenu' => 'required|string',
            'type' => 'required|in:actualite,evenement,education,culture',
           'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
            'date_evenement' => 'nullable|date',
            'lieu_evenement' => 'nullable|string|max:255',
            'statut' => 'sometimes|in:publie,brouillon'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except('image');
        
        // Valeurs par défaut
        $data['auteur'] = 'AJDCB';
        // Le slug sera généré automatiquement par le modèle

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/actualites');
            $data['image'] = str_replace('public/', '', $imagePath);
        }

        $actualite = Actualite::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Actualité créée avec succès',
            'data' => $actualite
        ], 201);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la création de l\'actualité',
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Mettre à jour une actualité
     */
    public function update(Request $request, $id)
    {
        try {
            $actualite = Actualite::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'titre' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'contenu' => 'sometimes|string',
                'type' => 'sometimes|in:actualite,evenement,education,culture',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
                'date_evenement' => 'nullable|date',
                'lieu_evenement' => 'nullable|string|max:255',
                'statut' => 'sometimes|in:publie,brouillon'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except('image');

            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image
                if ($actualite->image) {
                    Storage::delete('public/' . $actualite->image);
                }
                
                $imagePath = $request->file('image')->store('public/actualites');
                $data['image'] = str_replace('public/', '', $imagePath);
            }

            $actualite->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Actualité mise à jour avec succès',
                'data' => $actualite
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'actualité',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une actualité
     */
    public function destroy($id)
    {
        try {
            $actualite = Actualite::findOrFail($id);
            
            // Supprimer l'image
            if ($actualite->image) {
                Storage::delete('public/' . $actualite->image);
            }
            
            $actualite->delete();

            return response()->json([
                'success' => true,
                'message' => 'Actualité supprimée avec succès'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'actualité',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechercher des actualités
     */
    public function rechercher(Request $request)
    {
        try {
            $query = Actualite::query();
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('titre', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%")
                      ->orWhere('contenu', 'like', "%$search%");
                });
            }
            
            if ($request->has('type') && $request->type !== 'tous') {
                $query->where('type', $request->type);
            }
            
            if ($request->has('statut') && $request->statut !== 'tous') {
                $query->where('statut', $request->statut);
            }
            
            if ($request->has('date_debut') && $request->has('date_fin')) {
                $query->whereBetween('created_at', [$request->date_debut, $request->date_fin]);
            }
            
            $actualites = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'data' => $actualites,
                'message' => 'Recherche effectuée avec succès'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques des actualités
     */
    public function statistiques()
    {
        try {
            $stats = [
                'total' => Actualite::count(),
                'publiees' => Actualite::where('statut', 'publie')->count(),
                'brouillons' => Actualite::where('statut', 'brouillon')->count(),
                'par_type' => Actualite::selectRaw('type, count(*) as total')
                    ->groupBy('type')
                    ->get(),
                'dernieres_publications' => Actualite::orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(['id', 'titre', 'type', 'created_at'])
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistiques récupérées avec succès'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}