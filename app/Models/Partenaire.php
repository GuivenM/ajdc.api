<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partenaire extends Model
{
    use HasFactory;

    protected $table = 'partenaires';

    protected $fillable = [
        'nom',
        'description',
        'logo',
        'site_web',
        'type',
        'secteur_activite',
        'pays',
        'ville',
        'adresse',
        'email',
        'telephone',
        'date_debut_partenariat',
        'date_fin_partenariat',
        'niveau_partenariat',
        'domaines_intervention',
        'statut'
    ];

    protected $casts = [
        'domaines_intervention' => 'array',
        'date_debut_partenariat' => 'date',
        'date_fin_partenariat' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relations
     */
    // ATTENTION : la classe App\Models\Projet et la table pivot
    // 'partenaires_projets' n'existent pas dans ce projet. Cette relation
    // lèvera une erreur si elle est appelée. Créez le module "Projets" avant
    // d'utiliser ->projets(), ou retirez cette méthode.
    public function projets()
    {
        return $this->belongsToMany(Projet::class, 'partenaires_projets');
    }

    public function evenements()
    {
        return $this->belongsToMany(Evenement::class, 'partenaires_evenements');
    }

    /**
     * Scopes
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByNiveau($query, $niveau)
    {
        return $query->where('niveau_partenariat', $niveau);
    }

    /**
     * Accesseurs
     */
    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function getNiveauLabelAttribute()
    {
        $niveaux = [
            'or' => 'Partenariat Or',
            'argent' => 'Partenariat Argent',
            'bronze' => 'Partenariat Bronze',
            'institutionnel' => 'Partenariat Institutionnel',
            'technique' => 'Partenariat Technique'
        ];

        return $niveaux[$this->niveau_partenariat] ?? $this->niveau_partenariat;
    }

    public function getTypeLabelAttribute()
    {
        $types = [
            'institution' => 'Institution',
            'ong' => 'ONG',
            'entreprise' => 'Entreprise',
            'media' => 'Média',
            'universite' => 'Université/École',
            'association' => 'Association'
        ];

        return $types[$this->type] ?? $this->type;
    }
}