<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adhesion extends Model
{
    use HasFactory;

    protected $table = 'adhesions';

    protected $fillable = [
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'email',
        'telephone',
        'adresse',
        'ville',
        'profession',
        'niveau_etude',
        'etablissement',
        'motivation',
        'competences',
        'centres_interet',
        'disponibilite',
        'commentaire',
        'statut',
        'date_traitement',
        'commentaire_traitement',
        'traite_par'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_traitement' => 'datetime',
        'competences' => 'array',
        'centres_interet' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relations
     */
    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function membre()
    {
        return $this->hasOne(Membre::class, 'adhesion_id');
    }

    /**
     * Scopes
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeApprouvee($query)
    {
        return $query->where('statut', 'approuvee');
    }

    public function scopeRejetee($query)
    {
        return $query->where('statut', 'rejetee');
    }

    public function scopeByVille($query, $ville)
    {
        return $query->where('ville', 'like', "%$ville%");
    }

    /**
     * Accesseurs
     */
    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getAgeAttribute()
    {
        return $this->date_naissance->age;
    }

    public function getStatutLabelAttribute()
    {
        $labels = [
            'en_attente' => 'En attente',
            'approuvee' => 'Approuvée',
            'rejetee' => 'Rejetée'
        ];

        return $labels[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute()
    {
        $colors = [
            'en_attente' => 'warning',
            'approuvee' => 'success',
            'rejetee' => 'danger'
        ];

        return $colors[$this->statut] ?? 'secondary';
    }

    public function getDateSoumissionAttribute()
    {
        return $this->created_at->format('d/m/Y à H:i');
    }

    public function getDateNaissanceFormatAttribute()
    {
        return $this->date_naissance->format('d/m/Y');
    }
}