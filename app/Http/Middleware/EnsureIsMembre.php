<?php

namespace App\Http\Middleware;

use App\Models\Membre;
use Closure;
use Illuminate\Http\Request;

// Un token Sanctum valide peut appartenir soit à un User (espace admin),
// soit à un Membre (espace membre) — auth:sanctum seul ne fait pas la
// différence. Ce middleware bloque un token admin sur les routes membre
// (et empêchera symétriquement un token membre d'accéder aux routes admin,
// qui vérifient déjà $user->role via CheckRole — un Membre n'en a pas).
class EnsureIsMembre
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() instanceof Membre) {
            return response()->json([
                'success' => false,
                'message' => 'Accès réservé à l\'espace membre'
            ], 403);
        }

        return $next($request);
    }
}
