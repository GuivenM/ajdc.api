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
        'message' => 'API AJECB fonctionne correctement',
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

// routes/api.php
Route::get('/adhesions/{id}/details', [AdhesionController::class, 'show']);

// Routes pour les images (PUBLIQUES - sans authentification)
Route::prefix('images')->group(function () {
    Route::get('/{type}/{filename}', [ImageController::class, 'show']);
    Route::get('/{path}', [ImageController::class, 'get'])->where('path', '.*');
});

 Route::get('/statistiques', [ActionController::class, 'statistiques']);
         Route::get('/statistiques', [MessageController::class, 'statistiques']);
// ==================== ROUTES PROTÉGÉES (NÉCESSITENT AUTH) ====================
// ==================== ROUTES PROTÉGÉES ====================
// ==================== ROUTES PROTÉGÉES ====================
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    
    // Auth supplémentaires
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
    
    // ========== MESSAGES - ORDRE CORRECT ==========
    Route::prefix('messages')->group(function () {
        // 1. ROUTES SPÉCIFIQUES (sans paramètre) - EN PREMIER
 // ← À METTRE EN PREMIER
        Route::get('/', [MessageController::class, 'index']);
        Route::post('/', [MessageController::class, 'store']);
        
        // 2. ROUTES AVEC PARAMÈTRES - EN SECOND
        Route::get('/{id}', [MessageController::class, 'show']);
        Route::put('/{id}', [MessageController::class, 'update']);
        Route::delete('/{id}', [MessageController::class, 'destroy']);
        Route::post('/{id}/repondre', [MessageController::class, 'repondre']);
    });
    
    // ========== ACTIONS - MÊME LOGIQUE ==========
    Route::prefix('actions')->group(function () {
        // 1. ROUTES SPÉCIFIQUES D'ABORD
       
        Route::get('/section/{section}', [ActionController::class, 'getBySection']);
        Route::get('/', [ActionController::class, 'index']);
        Route::post('/', [ActionController::class, 'store']);
        
        // 2. ROUTES AVEC PARAMÈTRES ENSUITE
        Route::get('/{id}', [ActionController::class, 'show']);
        Route::post('/{id}', [ActionController::class, 'update']);
        Route::delete('/{id}', [ActionController::class, 'destroy']);
    });
    
    // ========== ADHÉSIONS ==========
    Route::prefix('adhesions')->group(function () {
        // 1. SPÉCIFIQUES D'ABORD
        Route::get('/statistiques', [AdhesionController::class, 'statistiques']);
        Route::get('/', [AdhesionController::class, 'index']);
        
        // 2. AVEC PARAMÈTRES ENSUITE
        Route::get('/{id}', [AdhesionController::class, 'show']);
        Route::put('/{id}/traiter', [AdhesionController::class, 'traiter']);
        Route::delete('/{id}', [AdhesionController::class, 'destroy']);
    });
    
    // ========== MEMBRES ==========
    Route::prefix('membres')->group(function () {
        // 1. SPÉCIFIQUES D'ABORD
        Route::get('/bureau', [MembreController::class, 'bureau']);
        Route::get('/commissions', [MembreController::class, 'commissions']);
        Route::get('/postes-bureau', [MembreController::class, 'postesBureau']);
        Route::get('/commission/{nom}', [MembreController::class, 'commission']);
        Route::get('/', [MembreController::class, 'index']);
        Route::post('/', [MembreController::class, 'store']);
        
        // 2. AVEC PARAMÈTRES ENSUITE
        Route::get('/{id}', [MembreController::class, 'show']);
        Route::put('/{id}', [MembreController::class, 'update']);
        Route::delete('/{id}', [MembreController::class, 'destroy']);
    });
});