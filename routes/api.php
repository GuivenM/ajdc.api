<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\ActionController;
use App\Http\Controllers\API\ActualiteController;
use App\Http\Controllers\API\AdhesionController;
use App\Http\Controllers\API\MembreController;
use App\Http\Controllers\API\CotisationController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MembreAuthController;
use App\Http\Controllers\API\MembreEspaceController;
use App\Http\Controllers\API\EvenementController;
use App\Http\Controllers\API\GuideController;
use App\Http\Controllers\API\PartenaireController;
use App\Http\Controllers\API\PaiementController;
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

// Auth (espace admin)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Auth (espace membre) — distinct de l'espace admin ci-dessus
Route::prefix('v1/membre/auth')->group(function () {
    Route::post('/activer-compte', [MembreAuthController::class, 'activerCompte']);
    Route::post('/login', [MembreAuthController::class, 'login']);
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

    // Événements - Routes publiques (consultation)
    Route::get('/evenements', [EvenementController::class, 'index']);
    Route::get('/evenements/{id}', [EvenementController::class, 'show']);

    // Guide - Routes publiques (arborescence sections > sous-sections > documents)
    Route::get('/guide', [GuideController::class, 'index']);
    Route::get('/guide/sections/{id}', [GuideController::class, 'showSection']);
    Route::post('/guide/documents/{id}/telecharger', [GuideController::class, 'telechargerDocument']);

    // Partenaires - Routes publiques (consultation)
    Route::get('/partenaires', [PartenaireController::class, 'index']);
    Route::get('/partenaires/{id}', [PartenaireController::class, 'show']);

    // Paiements FedaPay - Routes publiques (un visiteur ou un membre paie sans être connecté)
    Route::post('/paiements/cotisation', [PaiementController::class, 'initierCotisation']);
    Route::post('/paiements/evenements/{id}', [PaiementController::class, 'initierEvenement']);
});

// Webhook FedaPay (PUBLIC — appelé par les serveurs FedaPay, pas par le navigateur)
Route::post('/v1/paiements/webhook', [PaiementController::class, 'webhook']);

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

    // ========== COTISATIONS ==========
    // Données financières internes : lecture réservée aux comptes admin (pas de route publique).
    // Marquer payé/impayé : admin/super_admin uniquement.
    Route::prefix('cotisations')->group(function () {
        Route::get('/', [CotisationController::class, 'index']);
        Route::get('/statistiques', [CotisationController::class, 'statistiques']);
        Route::get('/export', [CotisationController::class, 'export']);
        Route::get('/membre/{id}', [CotisationController::class, 'historiqueMembre']);
        Route::post('/marquer', [CotisationController::class, 'marquer'])
            ->middleware('role:super_admin,admin');
    });

    // ========== MEMBRES ==========
    // Lecture de TOUS les membres (actifs + inactifs), réservé à l'espace admin.
    Route::get('/membres-admin/tous', [MembreController::class, 'tous']);
    Route::get('/membres-admin/export', [MembreController::class, 'export']);

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

    // ========== ÉVÉNEMENTS ==========
    // Lecture : déjà publique. Créer/modifier : admin/super_admin. Supprimer : super_admin uniquement.
    Route::prefix('evenements')->group(function () {
        Route::get('/statistiques', [EvenementController::class, 'statistiques'])
            ->middleware('role:super_admin,admin');
        Route::post('/', [EvenementController::class, 'store'])
            ->middleware('role:super_admin,admin');
        Route::put('/{id}', [EvenementController::class, 'update'])
            ->middleware('role:super_admin,admin');
        Route::delete('/{id}', [EvenementController::class, 'destroy'])
            ->middleware('role:super_admin');
    });

    // ========== GUIDE ==========
    // Lecture : déjà publique (y compris brouillons via ?all=1, réservé à l'admin
    // côté frontend). Créer/modifier/supprimer : admin/super_admin pour tout niveau
    // de la hiérarchie (sections, sous-sections, documents).
    Route::prefix('guide')->group(function () {
        Route::post('/sections', [GuideController::class, 'storeSection'])
            ->middleware('role:super_admin,admin');
        Route::put('/sections/{id}', [GuideController::class, 'updateSection'])
            ->middleware('role:super_admin,admin');
        Route::delete('/sections/{id}', [GuideController::class, 'destroySection'])
            ->middleware('role:super_admin');

        Route::post('/sous-sections', [GuideController::class, 'storeSousSection'])
            ->middleware('role:super_admin,admin');
        Route::put('/sous-sections/{id}', [GuideController::class, 'updateSousSection'])
            ->middleware('role:super_admin,admin');
        Route::delete('/sous-sections/{id}', [GuideController::class, 'destroySousSection'])
            ->middleware('role:super_admin');

        Route::post('/documents', [GuideController::class, 'storeDocument'])
            ->middleware('role:super_admin,admin');
        Route::post('/documents/{id}', [GuideController::class, 'updateDocument'])
            ->middleware('role:super_admin,admin');
        Route::delete('/documents/{id}', [GuideController::class, 'destroyDocument'])
            ->middleware('role:super_admin');
    });

    // ========== PARTENAIRES ==========
    // Lecture : déjà publique (partenaires actifs uniquement, sauf filtre explicite).
    // Créer/modifier : admin/super_admin. Supprimer : super_admin uniquement.
    Route::prefix('partenaires')->group(function () {
        Route::get('/statistiques', [PartenaireController::class, 'statistiques'])
            ->middleware('role:super_admin,admin');
        Route::post('/', [PartenaireController::class, 'store'])
            ->middleware('role:super_admin,admin');
        Route::put('/{id}', [PartenaireController::class, 'update'])
            ->middleware('role:super_admin,admin');
        Route::delete('/{id}', [PartenaireController::class, 'destroy'])
            ->middleware('role:super_admin');
    });
});

// ==================== ROUTES PROTÉGÉES — ESPACE MEMBRE ====================
// Séparées des routes admin ci-dessus : un token Membre ne peut pas accéder
// aux routes admin (elles vérifient $user->role, absent sur Membre), et le
// middleware 'membre' bloque symétriquement un token admin ici.
Route::middleware(['auth:sanctum', 'membre'])->prefix('v1/membre')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [MembreAuthController::class, 'logout']);
        Route::get('/me', [MembreAuthController::class, 'me']);
        Route::post('/change-password', [MembreAuthController::class, 'changePassword']);
    });

    Route::put('/profil', [MembreEspaceController::class, 'updateProfil']);
    Route::get('/mes-cotisations', [CotisationController::class, 'mesCotisations']);
    Route::get('/evenements', [MembreEspaceController::class, 'evenements']);
    Route::post('/evenements/{id}/inscription', [MembreEspaceController::class, 'inscrire']);
    Route::delete('/evenements/{id}/inscription', [MembreEspaceController::class, 'desinscrire']);
});