<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Afficher une image depuis le stockage
     */
    public function show($type, $filename)
    {
        try {
            // Chemins autorisés
            $allowedTypes = ['actions', 'membres', 'actualites'];
            
            if (!in_array($type, $allowedTypes)) {
                abort(404);
            }

            // Construire le chemin complet
            $path = 'public/' . $type . '/' . $filename;
            
            // Vérifier si le fichier existe
            if (!Storage::exists($path)) {
                abort(404);
            }
            
            // Récupérer le fichier et son type MIME
            $file = Storage::get($path);
            $mimeType = Storage::mimeType($path);
            
            // Retourner l'image avec les bons en-têtes
            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header('Cache-Control', 'public, max-age=86400');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image non trouvée'
            ], 404);
        }
    }

    /**
     * Méthode générique pour toutes les images
     *
     * ATTENTION SÉCURITÉ : l'ancienne version faisait
     * str_replace(['../', './'], '', $path), qui est un filtre bien connu et
     * contournable (ex: '..././' redevient '../' après un seul passage de
     * remplacement), ce qui permettait potentiellement de sortir du dossier
     * storage/app/public et de lire n'importe quel fichier lisible par PHP.
     * On résout désormais le chemin réel et on vérifie qu'il reste bien
     * contenu dans le dossier public du disque de stockage.
     */
    public function get($path)
    {
        try {
            $disk = Storage::disk('local');
            $baseDir = realpath($disk->path('public'));

            if ($baseDir === false) {
                abort(404);
            }

            $requestedPath = $disk->path('public/' . $path);
            $realRequestedPath = realpath($requestedPath);

            // Le chemin résolu doit exister ET rester strictement à l'intérieur
            // du dossier public autorisé.
            if (
                $realRequestedPath === false ||
                !str_starts_with($realRequestedPath, $baseDir . DIRECTORY_SEPARATOR)
            ) {
                abort(404);
            }

            $relativePath = 'public/' . ltrim(str_replace($baseDir, '', $realRequestedPath), DIRECTORY_SEPARATOR);

            if (!Storage::exists($relativePath)) {
                abort(404);
            }

            $file = Storage::get($relativePath);
            $mimeType = Storage::mimeType($relativePath);

            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Cache-Control', 'public, max-age=86400');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image non trouvée'
            ], 404);
        }
    }
}