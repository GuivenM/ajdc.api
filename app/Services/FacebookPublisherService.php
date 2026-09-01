<?php

namespace App\Services;

use App\Models\Actualite;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FacebookPublisherService
{
    /**
     * true si une Page + un token sont configurés (voir .env.example) —
     * permet au front de savoir s'il doit proposer l'auto-publication
     * ou seulement le lien de partage manuel.
     */
    public function isConfigured(): bool
    {
        return filled(config('services.facebook.page_id'))
            && filled(config('services.facebook.page_token'));
    }

    /**
     * Publie l'actualité sur la Page Facebook configurée, comme un post
     * natif (texte + photos réellement attachées), pas un lien partagé.
     * Retourne l'URL du post créé.
     *
     * @throws RuntimeException si l'API Facebook renvoie une erreur.
     */
    public function publier(Actualite $actualite): string
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException(
                "Aucune Page Facebook n'est configurée (FACEBOOK_PAGE_ID / FACEBOOK_PAGE_ACCESS_TOKEN)."
            );
        }

        $pageId = config('services.facebook.page_id');
        $token = config('services.facebook.page_token');
        $message = $this->buildMessage($actualite);
        $photoUrls = $actualite->photos_urls;

        if ($photoUrls->isEmpty()) {
            // Pas de photo : simple post texte, avec le lien de l'actualité
            // dans le message (pas de carte d'aperçu, conformément au fait
            // qu'on ne publie plus "juste un lien").
            $response = Http::asForm()->post("https://graph.facebook.com/v21.0/{$pageId}/feed", [
                'message' => $message,
                'access_token' => $token,
            ]);

            if ($response->failed()) {
                throw new RuntimeException($response->json('error.message') ?? 'Erreur inconnue de l\'API Facebook');
            }

            return $this->postUrl($pageId, $response->json('id'));
        }

        // 1 ou plusieurs photos : chaque photo est d'abord uploadée "non
        // publiée" (published=false), puis le post texte les attache toutes
        // en une fois — c'est ce qui donne un vrai post multi-photos natif
        // au lieu d'une image seule ou d'un lien.
        $mediaIds = [];
        foreach ($photoUrls as $url) {
            $upload = Http::asForm()->post("https://graph.facebook.com/v21.0/{$pageId}/photos", [
                'url' => $url,
                'published' => 'false',
                'access_token' => $token,
            ]);

            if ($upload->failed()) {
                throw new RuntimeException($upload->json('error.message') ?? 'Erreur lors de l\'envoi d\'une photo à Facebook');
            }

            $mediaIds[] = $upload->json('id');
        }

        $payload = [
            'message' => $message,
            'access_token' => $token,
        ];
        foreach ($mediaIds as $i => $mediaId) {
            $payload["attached_media[{$i}]"] = json_encode(['media_fbid' => $mediaId]);
        }

        $response = Http::asForm()->post("https://graph.facebook.com/v21.0/{$pageId}/feed", $payload);

        if ($response->failed()) {
            throw new RuntimeException($response->json('error.message') ?? 'Erreur inconnue de l\'API Facebook');
        }

        return $this->postUrl($pageId, $response->json('id'));
    }

    private function postUrl(string $pageId, ?string $postId): string
    {
        $numericPostId = $postId && str_contains($postId, '_') ? explode('_', $postId)[1] : $postId;
        return "https://www.facebook.com/{$pageId}/posts/{$numericPostId}";
    }

    private function buildMessage(Actualite $actualite): string
    {
        $texte = trim($actualite->titre . "\n\n" . $actualite->description);
        return $texte . "\n\n" . $actualite->lien_public;
    }
}
