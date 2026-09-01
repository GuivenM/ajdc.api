<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Actualite extends Model
{
    use HasFactory;

    protected $table = 'actualites';

    protected $fillable = [
        'titre',
        'slug',           // ← AJOUTER CE CHAMP
        'description',
        'contenu',
        'image',
        'type',
        'date_evenement',
        'lieu_evenement',
        'auteur',
        'statut',
        'facebook_post_url',
    ];

    protected $casts = [
        'date_evenement' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Boot method pour générer le slug automatiquement
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($actualite) {
            if (empty($actualite->slug)) {
                $actualite->slug = Str::slug($actualite->titre) . '-' . uniqid();
            }
        });
    }

    /**
     * Scopes
     */
    public function scopePublie($query)
    {
        return $query->where('statut', 'publie');
    }

    public function scopeBrouillon($query)
    {
        return $query->where('statut', 'brouillon');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecents($query, $limit = 5)
    {
        return $query->publie()->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Galerie multi-photos (nouveau). Le champ `image` legacy reste rempli
     * pour les actualités créées avant cette fonctionnalité — `image_url`
     * bascule automatiquement sur la 1ère photo de la galerie dès qu'il y
     * en a une, sans casser l'affichage des anciennes actualités.
     */
    public function photos()
    {
        return $this->hasMany(ActualitePhoto::class)->orderBy('ordre');
    }

    protected $appends = ['image_url', 'date', 'type_label', 'extrait', 'lien_public', 'photos_urls'];

    /**
     * Accesseurs
     */
    public function getImageUrlAttribute()
    {
        $premierePhoto = $this->relationLoaded('photos') ? $this->photos->first() : $this->photos()->first();
        if ($premierePhoto) {
            return $premierePhoto->url;
        }

        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function getPhotosUrlsAttribute()
    {
        $photos = $this->relationLoaded('photos') ? $this->photos : $this->photos()->get();

        if ($photos->isNotEmpty()) {
            return $photos->pluck('url')->values();
        }

        // Compat : anciennes actualités avec une seule image legacy.
        return $this->image ? collect([Storage::disk('public')->url($this->image)]) : collect();
    }

    public function getLienPublicAttribute()
    {
        $base = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
        return "{$base}/news/{$this->id}";
    }

    public function getDateAttribute()
    {
        return $this->created_at->format('d/m/Y');
    }

    public function getTypeLabelAttribute()
    {
        $types = [
            'actualite' => 'Actualité',
            'evenement' => 'Événement',
            'education' => 'Éducation',
            'culture' => 'Culture'
        ];

        return $types[$this->type] ?? $this->type;
    }

    public function getExtraitAttribute($longueur = 150)
    {
        return substr(strip_tags($this->description), 0, $longueur) . '...';
    }
}