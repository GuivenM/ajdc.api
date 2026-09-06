<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\JournalActivite;
use Illuminate\Http\Request;

class JournalActiviteController extends Controller
{
    /**
     * Liste paginée du journal, plus récent en premier.
     * GET /v1/journal-activite?page=1
     */
    public function index(Request $request)
    {
        try {
            $entrees = JournalActivite::with('user:id,nom,prenom')
                ->orderByDesc('created_at')
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $entrees->items(),
                    'meta' => [
                        'current_page' => $entrees->currentPage(),
                        'last_page' => $entrees->lastPage(),
                        'total' => $entrees->total(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du journal',
            ], 500);
        }
    }
}
