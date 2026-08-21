<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié. Veuillez vous connecter.'
            ], 401);
        }

        // Avant : ce middleware ne vérifiait que l'authentification, pas le rôle.
        // N'importe quel utilisateur connecté (y compris un modérateur) passait
        // ce contrôle. On vérifie désormais réellement le rôle admin.
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé aux administrateurs.'
            ], 403);
        }

        return $next($request);
    }
}