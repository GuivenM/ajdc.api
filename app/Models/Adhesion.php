<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adhesion extends Model
{
    use HasFactory;

    protected $table = 'adhesions';

    protected $fillable = [
        // Identité
        'nom', 'prenom', 'nom_marital', 'sexe', 'date_naissance', 'lieu_naissance',
        'situation_matrimoniale', 'nombre_enfants_charge',
        // Nationalité & pièces
        'nationalite', 'est_congolais', 'possede_carte_consulaire', 'carte_consulaire_fichier',
        'duree_au_benin', 'possede_cipr', 'cipr_fichier', 'photo',
        // Coordonnées
        'email', 'telephone', 'autre_telephone', 'adresse', 'ville',
        // Statut professionnel
        'profession', 'profession_autre', 'niveau_etude', 'niveau_etude_autre',
        'dernier_diplome', 'dernier_diplome_autre',
        // Entrepreneur
        'entrepreneur_domaine', 'entrepreneur_domaine_autre', 'entrepreneur_duree',
        'entrepreneur_nom_entreprise', 'entrepreneur_fonction',
        // Étudiant
        'etablissement', 'etudiant_filiere', 'etudiant_annee',
        // Compétences, intérêts, langues
        'competences', 'competences_autre', 'centres_interet', 'domaines_interet_autre',
        'loisirs', 'loisirs_autre', 'disponibilite', 'langues',
        // Engagement associatif
        'comment_connu', 'comment_connu_autre', 'recommande_par', 'motivation',
        'experience_associative', 'experience_associative_details', 'commissions_souhaitees', 'attentes',
        // Déclaration
        'declarant_nom_complet', 'accepte_conditions', 'souhaite_recevoir_actualites',
        'lettre_demande_fichiers',
        // Divers
        'commentaire',
        // Traitement
        'statut', 'date_traitement', 'commentaire_traitement', 'traite_par',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_traitement' => 'datetime',
        'competences' => 'array',
        'centres_interet' => 'array',
        'loisirs' => 'array',
        'langues' => 'array',
        'commissions_souhaitees' => 'array',
        'lettre_demande_fichiers' => 'array',
        'est_congolais' => 'boolean',
        'possede_carte_consulaire' => 'boolean',
        'possede_cipr' => 'boolean',
        'experience_associative' => 'boolean',
        'accepte_conditions' => 'boolean',
        'souhaite_recevoir_actualites' => 'boolean',
        'nombre_enfants_charge' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function membre()
    {
        return $this->hasOne(Membre::class, 'adhesion_id');
    }

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

    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getAgeAttribute()
    {
        return $this->date_naissance?->age;
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
        return $this->date_naissance?->format('d/m/Y');
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    public function getCarteConsulaireFichierUrlAttribute()
    {
        return $this->carte_consulaire_fichier ? asset('storage/' . $this->carte_consulaire_fichier) : null;
    }

    public function getCiprFichierUrlAttribute()
    {
        return $this->cipr_fichier ? asset('storage/' . $this->cipr_fichier) : null;
    }

    public function getLettreDemandeFichiersUrlsAttribute()
    {
        return collect($this->lettre_demande_fichiers ?? [])
            ->map(fn($path) => asset('storage/' . $path))
            ->values();
    }
}