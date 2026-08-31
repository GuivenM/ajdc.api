<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageCompressionService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Compresse et stocke une image uploadée sur le disque 'public'.
     * Retourne le chemin relatif, dans le même format que
     * str_replace('public/', '', $path) utilisé actuellement dans les contrôleurs.
     */
    public function store(UploadedFile $file, string $folder, int $maxWidth = 1600, int $quality = 75): string
    {
        $image = $this->manager->read($file->getRealPath());

        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        $filename = uniqid() . '.webp';
        $relativePath = $folder . '/' . $filename;

        Storage::disk('public')->put($relativePath, (string) $image->toWebp($quality));

        return $relativePath;
    }
}
