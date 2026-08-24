<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Adhesion;
use App\Mail\ConfirmationAdhesion;
use App\Mail\NotificationTraitementAdhesion;
use App\Mail\NotificationNouvelleAdhesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class AdhesionController extends Controller
{
    /**
     * Afficher toutes les demandes d'adhésion
     */
    public function index()
    {
        try {
            $adhesions = Adhesion::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $adhesions,
                'message' => 'Demandes d\'adhésion récupérées avec succès'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des demandes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une demande spécifique
     */
    public function show($id)
    {
        try {
            $adhesion = Adhesion::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $adhesion,
                'message' => 'Demande récupérée avec succès'
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Demande non trouvée'
            ], 404);
        }
    }

    /**
     * Créer une nouvelle demande d'adhésion
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'date_naissance' => 'required|date|before:today',
                'lieu_naissance' => 'required|string|max:255',
                'nationalite' => 'required|string|max:255',
                'email' => 'required|email|unique:adhesions,email',
                'telephone' => 'required|string|max:20',
                'adresse' => 'required|string',
                'ville' => 'required|string|max:255',
                'profession' => 'required|string|max:255',
                'niveau_etude' => 'required|string|max:255',
                'motivation' => 'required|string|min:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // IMPORTANT : on n'utilise QUE les champs validés (jamais $request->all()).
            // 'statut', 'date_traitement' et 'traite_par' sont fillable sur le modèle
            // mais ne doivent JAMAIS pouvoir être fixés par un appelant public, sinon
            // n'importe qui peut auto-approuver sa propre demande d'adhésion.
            $adhesion = Adhesion::create($validator->validated());

            // Envoyer email de confirmation au candidat
            try {
                Mail::to($adhesion->email)->send(new ConfirmationAdhesion($adhesion));
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email confirmation candidat: ' . $e->getMessage());
            }

            // Envoyer notification à l'admin
            try {
                $adminEmail = config('mail.admin_address');
                if (!$adminEmail) {
                    $adminEmail = 'contact@ajdcb.org';
                }
                Mail::to($adminEmail)->send(new NotificationNouvelleAdhesion($adhesion));
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email notification admin: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Demande d\'adhésion soumise avec succès. Un email de confirmation vous a été envoyé.',
                'data' => $adhesion,
                'redirect_url' => config('app.frontend_url') . '/admin/adhesions/' . $adhesion->id
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traiter une demande d'adhésion (approuver/rejeter)
     */
    public function traiter(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'statut' => 'required|in:approuvee,rejetee',
                'commentaire' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $adhesion = Adhesion::findOrFail($id);
            
            $oldStatut = $adhesion->statut;
            $adhesion->update([
                'statut' => $request->statut,
                'commentaire_traitement' => $request->commentaire,
                'date_traitement' => now()
            ]);

            // Envoyer email de notification
            try {
                Mail::to($adhesion->email)->send(new NotificationTraitementAdhesion($adhesion));
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email traitement: ' . $e->getMessage());
            }

            $message = $request->statut === 'approuvee' 
                ? 'Demande approuvée avec succès. Un email a été envoyé au candidat.'
                : 'Demande rejetée avec succès. Un email a été envoyé au candidat.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $adhesion,
                'redirect_url' => config('app.frontend_url') . '/admin/adhesions/' . $adhesion->id
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approuver une demande d'adhésion
     */
    public function approuver($id)
    {
        try {
            $adhesion = Adhesion::findOrFail($id);
            
            if ($adhesion->statut !== 'en_attente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette demande a déjà été traitée'
                ], 400);
            }
            
            $adhesion->update([
                'statut' => 'approuvee',
                'date_traitement' => now()
            ]);

            // Envoyer email de notification
            try {
                Mail::to($adhesion->email)->send(new NotificationTraitementAdhesion($adhesion));
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Demande approuvée avec succès',
                'data' => $adhesion,
                'redirect_url' => config('app.frontend_url') . '/admin/adhesions/' . $adhesion->id
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rejeter une demande d'adhésion
     */
    public function rejeter(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'motif' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $adhesion = Adhesion::findOrFail($id);
            
            if ($adhesion->statut !== 'en_attente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette demande a déjà été traitée'
                ], 400);
            }
            
            $adhesion->update([
                'statut' => 'rejetee',
                'commentaire_traitement' => $request->motif,
                'date_traitement' => now()
            ]);

            // Envoyer email de notification
            try {
                Mail::to($adhesion->email)->send(new NotificationTraitementAdhesion($adhesion));
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Demande rejetée avec succès',
                'data' => $adhesion,
                'redirect_url' => config('app.frontend_url') . '/admin/adhesions/' . $adhesion->id
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ... (les autres méthodes restent identiques)


    /**
     * Exporter les demandes en CSV
     */
    public function exporter(Request $request)
    {
        try {
            $query = Adhesion::query();
            
            if ($request->has('statut') && $request->statut !== 'tous') {
                $query->where('statut', $request->statut);
            }
            
            $adhesions = $query->orderBy('created_at', 'desc')->get();
            
            $filename = "adhesions_ajdcb_" . now()->format('Y-m-d') . ".csv";
            $handle = fopen('php://temp', 'w');
            
            // En-têtes CSV
            fputcsv($handle, [
                'ID', 'Nom', 'Prénom', 'Email', 'Téléphone', 
                'Ville', 'Profession', 'Statut', 'Date soumission', 'Date traitement'
            ]);
            
            foreach ($adhesions as $adhesion) {
                fputcsv($handle, [
                    $adhesion->id,
                    $adhesion->nom,
                    $adhesion->prenom,
                    $adhesion->email,
                    $adhesion->telephone,
                    $adhesion->ville,
                    $adhesion->profession,
                    $adhesion->statut,
                    $adhesion->created_at->format('d/m/Y H:i'),
                    $adhesion->date_traitement ? $adhesion->date_traitement->format('d/m/Y H:i') : ''
                ]);
            }
            
            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);
            
            return response($content)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}