<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Adhesion;
use App\Models\Membre;
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
     * Créer une nouvelle demande d'adhésion.
     *
     * Reflète le formulaire complet (ex Google Form) : nationalité et pièces,
     * état civil, statut professionnel avec branches étudiant/entrepreneur,
     * compétences/centres d'intérêt/langues, engagement associatif, et la
     * déclaration sur l'honneur. 4 uploads de fichiers (photo, carte
     * consulaire, CIPR, lettre au Président).
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                // --- Nationalité & pièces ---
                'est_congolais' => 'required|boolean',
                'nationalite' => 'nullable|string|max:255',
                'possede_carte_consulaire' => 'nullable|boolean',
                'duree_au_benin' => 'required|string|max:100',
                'possede_cipr' => 'nullable|boolean',

                // --- Identité & état civil ---
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'nom_marital' => 'nullable|string|max:255',
                'sexe' => 'required|in:masculin,feminin',
                'date_naissance' => 'required|date|before:today',
                'lieu_naissance' => 'required|string|max:255',
                'adresse' => 'required|string',
                'ville' => 'required|string|max:255',
                'situation_matrimoniale' => 'required|in:marie,divorce,union_libre,celibataire,veuf',
                'nombre_enfants_charge' => 'required|integer|min:0',

                // --- Pièces jointes ---
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240',
                'carte_consulaire_fichier' => 'nullable|required_if:possede_carte_consulaire,1|file|mimes:jpeg,png,jpg,pdf|max:10240',
                'cipr_fichier' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',

                // --- Statut professionnel ---
                'profession' => 'required|in:etudiant,employe,entrepreneur,commercant,sans_emploi,autre',
                'profession_autre' => 'nullable|required_if:profession,autre|string|max:255',
                'niveau_etude' => 'required|string|max:255',
                'niveau_etude_autre' => 'nullable|string|max:255',
                'dernier_diplome' => 'required|string|max:255',
                'dernier_diplome_autre' => 'nullable|string|max:255',

                // --- Branche Entrepreneur (si profession = entrepreneur) ---
                'entrepreneur_domaine' => 'nullable|required_if:profession,entrepreneur|string|max:255',
                'entrepreneur_domaine_autre' => 'nullable|string|max:255',
                'entrepreneur_duree' => 'nullable|required_if:profession,entrepreneur|string|max:100',
                'entrepreneur_nom_entreprise' => 'nullable|required_if:profession,entrepreneur|string|max:255',
                'entrepreneur_fonction' => 'nullable|string|max:255',

                // --- Branche Étudiant (si profession = etudiant) ---
                'etablissement' => 'nullable|required_if:profession,etudiant|string|max:255',
                'etudiant_filiere' => 'nullable|required_if:profession,etudiant|string|max:255',
                'etudiant_annee' => 'nullable|required_if:profession,etudiant|string|max:50',

                // --- Compétences, centres d'intérêt, loisirs, langues ---
                'competences' => 'required|array|min:1',
                'competences.*' => 'string|max:255',
                'competences_autre' => 'nullable|string|max:255',
                'centres_interet' => 'nullable|array',
                'centres_interet.*' => 'string|max:255',
                'domaines_interet_autre' => 'nullable|string|max:255',
                'loisirs' => 'required|array|min:1',
                'loisirs.*' => 'string|max:255',
                'loisirs_autre' => 'nullable|string|max:255',
                'disponibilite' => 'required|string|max:255',
                'langues' => 'nullable|array',
                'langues.*' => 'string|max:255',

                // --- Engagement associatif ---
                'comment_connu' => 'required|string|max:255',
                'comment_connu_autre' => 'nullable|string|max:255',
                'recommande_par' => 'nullable|string|max:255',
                'motivation' => 'nullable|string',
                'experience_associative' => 'required|boolean',
                'experience_associative_details' => 'nullable|required_if:experience_associative,1|string',
                'commissions_souhaitees' => 'nullable|array',
                'commissions_souhaitees.*' => 'string|max:255',
                'attentes' => 'nullable|string',

                // --- Coordonnées ---
                'email' => 'required|email|unique:adhesions,email',
                'telephone' => 'required|string|max:20',
                'autre_telephone' => 'nullable|string|max:20',

                // --- Déclaration sur l'honneur ---
                'declarant_nom_complet' => 'required|string|max:255',
                'accepte_conditions' => 'required|accepted',
                'souhaite_recevoir_actualites' => 'required|boolean',
                'lettre_demande_fichiers' => 'nullable|array|max:5',
                'lettre_demande_fichiers.*' => 'file|mimes:jpeg,png,jpg,pdf,doc,docx|max:20480',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // IMPORTANT : on part UNIQUEMENT des champs validés (jamais $request->all()).
            // 'statut', 'date_traitement' et 'traite_par' ne sont pas dans les règles
            // ci-dessus et ne peuvent donc jamais être fixés par un appelant public —
            // sinon n'importe qui pourrait auto-approuver sa propre demande.
            $data = $validator->validated();

            // Nationalité : déduite de la case "êtes-vous congolais(e)" si non précisée.
            $data['nationalite'] = $data['est_congolais']
                ? 'Congolaise'
                : ($data['nationalite'] ?? 'Non précisée');

            // Photo (obligatoire, utilisée pour le badge)
            $data['photo'] = str_replace('public/', '', $request->file('photo')->store('adhesions/photos', 'public'));

            // Carte consulaire (si le candidat déclare en posséder une)
            if ($request->hasFile('carte_consulaire_fichier')) {
                $data['carte_consulaire_fichier'] = str_replace(
                    'public/', '', $request->file('carte_consulaire_fichier')->store('adhesions/documents', 'public')
                );
            } else {
                unset($data['carte_consulaire_fichier']);
            }

            // CIPR (optionnel)
            if ($request->hasFile('cipr_fichier')) {
                $data['cipr_fichier'] = str_replace(
                    'public/', '', $request->file('cipr_fichier')->store('adhesions/documents', 'public')
                );
            } else {
                unset($data['cipr_fichier']);
            }

            // Lettre de demande au Président (jusqu'à 5 fichiers)
            if ($request->hasFile('lettre_demande_fichiers')) {
                $data['lettre_demande_fichiers'] = collect($request->file('lettre_demande_fichiers'))
                    ->map(fn($file) => str_replace('public/', '', $file->store('adhesions/lettres', 'public')))
                    ->values()
                    ->all();
            } else {
                unset($data['lettre_demande_fichiers']);
            }

            $adhesion = Adhesion::create($data);

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

            $adhesion->update([
                'statut' => $request->statut,
                'commentaire_traitement' => $request->commentaire,
                'date_traitement' => now(),
                'traite_par' => auth()->id(),
            ]);

            if ($request->statut === 'approuvee') {
                // Le membre existe dès l'approbation, mais reste "en_attente_paiement"
                // tant que la cotisation initiale (1000F, cf. PaiementController) n'est
                // pas réglée. firstOrCreate évite un doublon si traiter() est rappelé.
                Membre::firstOrCreate(
                    ['adhesion_id' => $adhesion->id],
                    [
                        'nom' => $adhesion->nom,
                        'prenom' => $adhesion->prenom,
                        'photo' => $adhesion->photo,
                        'whatsapp' => $adhesion->telephone,
                        'statut' => 'en_attente_paiement',
                    ]
                );
            }

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
                'date_traitement' => now(),
                'traite_par' => auth()->id(),
            ]);

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

    /**
     * Statistiques des demandes d'adhésion
     */
    public function statistiques()
    {
        try {
            $stats = [
                'total' => Adhesion::count(),
                'en_attente' => Adhesion::where('statut', 'en_attente')->count(),
                'approuvees' => Adhesion::where('statut', 'approuvee')->count(),
                'rejetees' => Adhesion::where('statut', 'rejetee')->count(),
                'par_ville' => Adhesion::selectRaw('ville, count(*) as total')
                    ->groupBy('ville')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une demande d'adhésion
     */
    public function destroy($id)
    {
        try {
            $adhesion = Adhesion::findOrFail($id);
            $adhesion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Demande supprimée avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
