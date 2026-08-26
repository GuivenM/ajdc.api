<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evenement extends Model
{
    use HasFactory;

    protected $table = 'evenements';

    protected $fillable = [
        'titre',
        'description',
        'contenu',
        'image',
        'date_debut',
        'date_fin',
        'heure_debut',
        'heure_fin',
        'lieu',
        'adresse',
        'ville',
        'type',
        'categorie',
        'capacite_max',
        'nombre_inscrits',
        'prix',
        'devise',
        'lien_billet',
        'organisateur',
        'contact_organisateur',
        'email_contact',
        'telephone_contact',
        'documents',
        'statut'
    ];

    protected $casts = [
        'documents' => 'array',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'heure_debut' => 'datetime',
        'heure_fin' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relations
     */
    public function participants()
    {
        return $this->belongsToMany(Membre::class, 'participations')
                    ->withPivot('statut', 'date_inscription', 'commentaire')
                    ->withTimestamps();
    }

    public function partenaires()
    {
        return $this->belongsToMany(Partenaire::class, 'partenaires_evenements');
    }

    // ATTENTION : la classe App\Models\EvenementMedia n'existe pas dans ce
    // projet. Cette relation lèvera une erreur "Class not found" si elle est
    // appelée. Créez le modèle + la migration correspondante (galerie photos
    // de l'événement) avant d'utiliser ->galerie(), ou retirez cette méthode.
    public function galerie()
    {
        return $this->hasMany(EvenementMedia::class);
    }

    /**
     * Scopes
     */
    public function scopeAVenir($query)
    {
        return $query->where('date_debut', '>=', now())
                     ->where('statut', 'publie');
    }

    public function scopePasses($query)
    {
        return $query->where('date_fin', '<', now())
                     ->where('statut', 'publie');
    }

    public function scopeEnCours($query)
    {
        return $query->where('date_debut', '<=', now())
                     ->where('date_fin', '>=', now())
                     ->where('statut', 'publie');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    protected $appends = ['image_url', 'places_restantes', 'est_complet', 'statut_evenement', 'periode'];

    /**
     * Accesseurs
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function getPlacesRestantesAttribute()
    {
        if (!$this->capacite_max) {
            return null;
        }
        return $this->capacite_max - $this->nombre_inscrits;
    }

    public function getEstCompletAttribute()
    {
        if (!$this->capacite_max) {
            return false;
        }
        return $this->nombre_inscrits >= $this->capacite_max;
    }

    public function getStatutEvenementAttribute()
    {
        if ($this->date_fin < now()) {
            return 'passé';
        }
        if ($this->date_debut > now()) {
            return 'à_venir';
        }
        return 'en_cours';
    }

    public function getPeriodeAttribute()
    {
        $debut = $this->date_debut->format('d/m/Y');
        $fin = $this->date_fin->format('d/m/Y');
        
        if ($debut === $fin) {
            return $debut . ' de ' . $this->heure_debut->format('H:i') . ' à ' . $this->heure_fin->format('H:i');
        }
        
        return 'Du ' . $debut . ' au ' . $fin;
    }
}