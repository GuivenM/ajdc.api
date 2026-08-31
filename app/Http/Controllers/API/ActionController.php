<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageCompressionService;

class ActionController extends Controller
{
    /**
     * Afficher toutes les actions
     */
    public function index(Request $request)
    {
        try {
            $query = Action::query();
            
            // Filtre par section
            if ($request->has('section')) {
                $query->where('section', $request->section);
            }
            
            // Filtre par statut
            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }
            
            $actions = $query->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $actions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des actions'
            ], 500);
        }
    }

    /**
     * Afficher les actions par section
     */
    public function getBySection($section)
    {
        try {
            $actions = Action::where('section', $section)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $actions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des actions'
            ], 500);
        }
    }

    /**
     * Afficher une action spécifique
     */
    public function show($id)
    {
        try {
            $action = Action::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $action
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Action non trouvée'
            ], 404);
        }
    }

    /**
     * Créer une nouvelle action
     */
    public function store(Request $request, ImageCompressionService $compressor)
    {
        try {
            $validator = Validator::make($request->all(), [
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'section' => 'required|in:solidarite,education,culture,communication',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'date_evenement' => 'nullable|date',
                'lieu' => 'nullable|string|max:255',
                'objectifs' => 'nullable|array',
                'activites_cles' => 'nullable|array',
                'resultats' => 'nullable|array',
                'statut' => 'sometimes|in:actif,inactif,a_venir,termine'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except('image');

            // Gérer l'upload de l'image
            if ($request->hasFile('image')) {
                $data['image'] = $compressor->store($request->file('image'), 'actions');
            }

            // Les champs objectifs / activites_cles / resultats sont castés en
            // 'array' sur le modèle Action : Eloquent se charge de l'encodage
            // JSON automatiquement, inutile (et incorrect) de le faire ici.


            $action = Action::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Action créée avec succès',
                'data' => $action
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
     * Mettre à jour une action
     */
    public function update(Request $request, $id, ImageCompressionService $compressor)
    {
        try {
            $action = Action::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'titre' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'section' => 'sometimes|in:solidarite,education,culture,communication',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:20480',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'date_evenement' => 'nullable|date',
                'lieu' => 'nullable|string|max:255',
                'objectifs' => 'nullable|array',
                'activites_cles' => 'nullable|array',
                'resultats' => 'nullable|array',
                'statut' => 'sometimes|in:actif,inactif,a_venir,termine'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except('image');

            // Gérer la nouvelle image
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image
                if ($action->image) {
                    Storage::delete('public/' . $action->image);
                }
                
                $data['image'] = $compressor->store($request->file('image'), 'actions');
            }

            $action->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Action mise à jour avec succès',
                'data' => $action
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
     * Supprimer une action
     */
    public function destroy($id)
    {
        try {
            $action = Action::findOrFail($id);
            
            // Supprimer l'image
            if ($action->image) {
                Storage::delete('public/' . $action->image);
            }
            
            $action->delete();

            return response()->json([
                'success' => true,
                'message' => 'Action supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    /**
     * Statistiques des actions
     */
    public function statistiques()
    {
        try {
            $stats = [
                'total' => Action::count(),
                'par_section' => Action::selectRaw('section, count(*) as total')
                    ->groupBy('section')
                    ->get(),
                'par_statut' => Action::selectRaw('statut, count(*) as total')
                    ->groupBy('statut')
                    ->get(),
                'prochaines_actions' => Action::where('statut', 'a_venir')
                    ->orWhere('date_debut', '>', now())
                    ->orderBy('date_debut')
                    ->limit(5)
                    ->get(['id', 'titre', 'date_debut', 'section'])
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