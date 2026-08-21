<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\ActionController;
use App\Http\Controllers\API\ActualiteController;
use App\Http\Controllers\API\AdhesionController;
use App\Http\Controllers\API\MembreController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\ImageController;

// ==================== ROUTES PUBLIQUES ====================

// Test
Route::get('/test', function() {
    return response()->json([
        'success' => true,
        'message' => 'API AJDCB fonctionne correctement',
        'version' => '1.0.0',
        'timestamp' => now()->toDateTimeString()
    ]);
});

// Auth
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Messages - Routes publiques (création et consultation publique)
Route::prefix('v1')->group(function () {
    // Créer un message (PUBLIC)
    Route::post('/messages', [MessageController::class, 'store']);
    
    // Voir un message spécifique (PUBLIC si vous voulez)
    Route::get('/messages/{id}', [MessageController::class, 'show']);
    
    // Adhésions - Créer une demande (PUBLIC)
    Route::post('/adhesions', [AdhesionController::class, 'store']);
    
    // Actualités - Routes publiques
    Route::get('/actualites', [ActualiteController::class, 'index']);
    Route::get('/actualites/type/{type}', [ActualiteController::class, 'getByType']);
    Route::get('/actualites/dernieres', [ActualiteController::class, 'dernieresActualites']);
    Route::get('/actualites/{id}', [ActualiteController::class, 'show']);
    
    // Actions - Routes publiques (consultation)
    Route::get('/actions', [ActionController::class, 'index']);
    Route::get('/actions/section/{section}', [ActionController::class, 'getBySection']);
    Route::get('/actions/{id}', [ActionController::class, 'show']);
    
    // Membres - Routes publiques
    Route::get('/membres', [MembreController::class, 'index']);
    Route::get('/membres/bureau', [MembreController::class, 'bureau']);
    Route::get('/membres/commissions', [MembreController::class, 'commissions']);
    Route::get('/membres/commission/{nom}', [MembreController::class, 'commission']);
    Route::get('/membres/postes-bureau', [MembreController::class, 'postesBureau']);
    Route::get('/membres/{id}', [MembreController::class, 'show']);
});

// Détail d'une adhésion (PUBLIC — ex : page de suivi de candidature par lien direct)
Route::get('/adhesions/{id}/details', [AdhesionController::class, 'show']);

// Routes pour les images (PUBLIQUES - sans authentification)
Route::prefix('images')->group(function () {
    Route::get('/{type}/{filename}', [ImageController::class, 'show']);
    Route::get('/{path}', [ImageController::class, 'get'])->where('path', '.*');
});

// ==================== ROUTES PROTÉGÉES (NÉCESSITENT AUTH) ====================
// Rôles disponibles : super_admin, admin, moderateur (voir AuthController::getPermissionsByRole)
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Auth supplémentaires
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // ========== MESSAGES ==========
    // Lecture : les 3 rôles. Répondre : admin/super_admin. Supprimer : super_admin uniquement.
    Route::prefix('messages')->group(function () {
        Route::get('/', [MessageController::class, 'index']);
        Route::get('/statistiques', [MessageController::class, 'statistiques']);
        Route::get('/{id}', [MessageController::class, 'show']);
        Route::put('/{id}', [MessageController::class, 'update'])
            ->middleware('role:super_admin,admin');
        Route::post('/{id}/repondre', [MessageController::class, 'repondre'])
            ->middleware('role:super_admin,admin');
        Route::delete('/{id}', [MessageController::class, 'destroy'])
            ->middleware('role:super_admin');
    });

    // ========== ACTUALITÉS ==========
    // Lecture : les 3 rôles. Créer : moderateur/admin/super_admin. Modifier : admin/super_admin.
    // Supprimer : super_admin uniquement.
    Route::prefix('actualites')->group(function () {
        Route::get('/statistiques', [ActualiteController::class, 'statistiques']);
        Route::post('/', [ActualiteController::class, 'store'])
            ->middleware('role:super_admin,admin,moderateur');
        Route::put('/{id}', [ActualiteController::class, 'update'])
            ->middleware('role:super_admin,admin');
        Route::delete('/{id}', [ActualiteController::class, 'destroy'])
            ->middleware('role:super_admin');
    });

    // ========== ACTIONS ==========
    // Lecture : les 3 rôles (déjà publique). Créer/modifier : admin/super_admin.
    // Supprimer : super_admin uniquement.
    Route::prefix('actions')->group(function () {
        Route::get('/statistiques', [ActionController::class, 'statistiques']);
        Route::post('/', [ActionController::class, 'store'])
            ->middleware('role:super_admin,admin');
        Route::post('/{id}', [ActionController::class, 'update'])
            ->middleware('role:super_admin,admin');
        Route::delete('/{id}', [ActionController::class, 'destroy'])
            ->middleware('role:super_admin');
    });

    // ========== ADHÉSIONS ==========
    // Lecture : les 3 rôles. Traiter (approuver/rejeter) : admin/super_admin.
    // Supprimer : super_admin uniquement.
    Route::prefix('adhesions')->group(function () {
        Route::get('/statistiques', [AdhesionController::class, 'statistiques']);
        Route::get('/', [AdhesionController::class, 'index']);
        Route::get('/{id}', [AdhesionController::class, 'show']);
        Route::put('/{id}/traiter', [AdhesionController::class, 'traiter'])
            ->middleware('role:super_admin,admin');
        Route::delete('/{id}', [AdhesionController::class, 'destroy'])
            ->middleware('role:super_admin');
    });

    // ========== MEMBRES ==========
    // Lecture (bureau, commissions, etc.) : déjà publique. Créer/modifier : admin/super_admin.
    // Supprimer : super_admin uniquement.
    Route::prefix('membres')->group(function () {
        Route::post('/', [MembreController::class, 'store'])
            ->middleware('role:super_admin,admin');
        Route::put('/{id}', [MembreController::class, 'update'])
            ->middleware('role:super_admin,admin');
        Route::delete('/{id}', [MembreController::class, 'destroy'])
            ->middleware('role:super_admin');
    });
});