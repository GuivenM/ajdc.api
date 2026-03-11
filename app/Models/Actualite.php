<?php

namespace App\Models;

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
        'statut'
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
     * Accesseurs
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
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