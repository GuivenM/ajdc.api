<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'section',
        'image',
        'date_debut',
        'date_fin',
        'date_evenement',
        'lieu',
        'objectifs',
        'activites_cles',
        'resultats',
        'statut'
    ];

    protected $casts = [
        'objectifs' => 'array',
        'activites_cles' => 'array',
        'resultats' => 'array',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_evenement' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relations
     */
    public function medias()
    {
        return $this->hasMany(ActionMedia::class);
    }

    /**
     * Scopes
     */
    public function scopeBySection($query, $section)
    {
        return $query->where('section', $section);
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeAVenir($query)
    {
        return $query->where('statut', 'a_venir');
    }

    public function scopeTermine($query)
    {
        return $query->where('statut', 'termine');
    }

    /**
     * Accesseurs
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getSectionLabelAttribute()
    {
        $labels = [
            'solidarite' => 'Solidarité & Intégration',
            'education' => 'Éducation & Formation',
            'culture' => 'Culture & Identité',
            'communication' => 'Communication & Partenariats'
        ];

        return $labels[$this->section] ?? $this->section;
    }

    public function getStatutLabelAttribute()
    {
        $labels = [
            'actif' => 'En cours',
            'inactif' => 'Inactif',
            'a_venir' => 'À venir',
            'termine' => 'Terminé'
        ];

        return $labels[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute()
    {
        $colors = [
            'actif' => 'success',
            'inactif' => 'danger',
            'a_venir' => 'warning',
            'termine' => 'secondary'
        ];

        return $colors[$this->statut] ?? 'primary';
    }

    public function getDateAffichageAttribute()
    {
        if ($this->date_evenement) {
            return $this->date_evenement->format('d/m/Y');
        }
        
        if ($this->date_debut && $this->date_fin) {
            return 'Du ' . $this->date_debut->format('d/m/Y') . ' au ' . $this->date_fin->format('d/m/Y');
        }
        
        if ($this->date_debut) {
            return 'À partir du ' . $this->date_debut->format('d/m/Y');
        }
        
        return 'Date non définie';
    }

    public function getLieuAffichageAttribute()
    {
        return $this->lieu ?? 'Lieu non défini';
    }

    public function getObjectifsListeAttribute()
    {
        return is_array($this->objectifs) ? $this->objectifs : [];
    }

    public function getActivitesListeAttribute()
    {
        return is_array($this->activites_cles) ? $this->activites_cles : [];
    }

    public function getResultatsListeAttribute()
    {
        return is_array($this->resultats) ? $this->resultats : [];
    }
}