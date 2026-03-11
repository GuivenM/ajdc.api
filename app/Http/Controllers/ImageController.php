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
     */
    public function get($path)
    {
        try {
            // Nettoyer le chemin
            $cleanPath = str_replace(['../', './'], '', $path);
            $fullPath = 'public/' . $cleanPath;
            
            if (!Storage::exists($fullPath)) {
                abort(404);
            }
            
            $file = Storage::get($fullPath);
            $mimeType = Storage::mimeType($fullPath);
            
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