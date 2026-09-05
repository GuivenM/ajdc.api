<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\ActivationCompteAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UtilisateurController extends Controller
{
    private const ROLES_VALIDES = ['super_admin', 'admin', 'moderateur', 'tresorier'];

    /**
     * Liste de tous les comptes admin (super_admin uniquement — voir routes/api.php).
     */
    public function index()
    {
        try {
            $utilisateurs = User::with('membre:id,nom,prenom,poste')
                ->orderByRaw("FIELD(role, 'super_admin', 'admin', 'tresorier', 'moderateur')")
                ->orderBy('nom')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $utilisateurs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs',
            ], 500);
        }
    }

    /**
     * Modifier le rôle et/ou le statut actif d'un compte. On bloque volontairement
     * qu'un super_admin se désactive ou se rétrograde lui-même par erreur —
     * il faut qu'un autre super_admin s'en charge.
     */
    public function update(Request $request, $id)
    {
        try {
            $utilisateur = User::find($id);

            if (!$utilisateur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'role' => 'sometimes|in:' . implode(',', self::ROLES_VALIDES),
                'est_actif' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $estSoiMeme = $request->user()->id === $utilisateur->id;

            if ($estSoiMeme && $request->has('est_actif') && !$request->boolean('est_actif')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas désactiver votre propre compte. Demandez à un autre super administrateur.',
                ], 422);
            }

            if ($estSoiMeme && $request->has('role') && $request->role !== 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas changer votre propre rôle. Demandez à un autre super administrateur.',
                ], 422);
            }

            $utilisateur->update($request->only(['role', 'est_actif']));

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur mis à jour',
                'data' => $utilisateur->fresh('membre:id,nom,prenom,poste'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'utilisateur',
            ], 500);
        }
    }

    /**
     * Supprimer un compte admin. On ne peut pas se supprimer soi-même.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $utilisateur = User::find($id);

            if (!$utilisateur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable',
                ], 404);
            }

            if ($request->user()->id === $utilisateur->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
                ], 422);
            }

            $utilisateur->delete();

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur supprimé',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'utilisateur',
            ], 500);
        }
    }

    /**
     * Régénère le lien d'activation et renvoie l'email, pour un compte qui ne
     * s'est encore jamais activé (utile si l'email initial s'est perdu, ou
     * si le lien de 7 jours a expiré).
     */
    public function renvoyerActivation($id)
    {
        try {
            $utilisateur = User::find($id);

            if (!$utilisateur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable',
                ], 404);
            }

            if (is_null($utilisateur->activation_token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce compte est déjà activé.',
                ], 422);
            }

            $utilisateur->update([
                'activation_token' => Str::random(64),
                'activation_token_expire_at' => now()->addDays(7),
            ]);

            Mail::to($utilisateur->email)->send(new ActivationCompteAdmin($utilisateur));

            return response()->json([
                'success' => true,
                'message' => 'Email d\'activation renvoyé à ' . $utilisateur->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du renvoi de l\'email d\'activation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
