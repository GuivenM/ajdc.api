<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'objet',
        'message',
        'reponse',
        'date_reponse',
        'statut',
        'lu_le',
        'traite_par'
    ];

    protected $casts = [
        'lu_le' => 'datetime',
        'date_reponse' => 'datetime',
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

    /**
     * Scopes
     */
    public function scopeNonLu($query)
    {
        return $query->where('statut', 'non_lu');
    }

    public function scopeLu($query)
    {
        return $query->where('statut', 'lu');
    }

    public function scopeRepondu($query)
    {
        return $query->where('statut', 'repondu');
    }

    public function scopeByObjet($query, $objet)
    {
        return $query->where('objet', $objet);
    }

    /**
     * Accesseurs
     */
    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getObjetLabelAttribute()
    {
        $objets = [
            'question' => 'Question',
            'partenariat' => 'Demande de partenariat',
            'adhesion' => 'Demande d\'adhésion',
            'urgence' => 'Urgence communautaire',
            'information' => 'Demande d\'information',
            'reclamation' => 'Réclamation',
            'autre' => 'Autre'
        ];

        return $objets[$this->objet] ?? $this->objet;
    }

    public function getStatutLabelAttribute()
    {
        $labels = [
            'non_lu' => 'Non lu',
            'lu' => 'Lu',
            'repondu' => 'Répondu'
        ];

        return $labels[$this->statut] ?? $this->statut;
    }

    public function getStatutColorAttribute()
    {
        $colors = [
            'non_lu' => 'danger',
            'lu' => 'warning',
            'repondu' => 'success'
        ];

        return $colors[$this->statut] ?? 'secondary';
    }

    public function getDateEnvoiAttribute()
    {
        return $this->created_at->format('d/m/Y à H:i');
    }

    public function getDateEnvoiFrAttribute()
    {
        return $this->created_at->locale('fr')->isoFormat('LLL');
    }

    public function getExtraitAttribute($longueur = 100)
    {
        return substr($this->message, 0, $longueur) . '...';
    }

    /**
     * Marquer comme lu
     */
    public function marquerCommeLu()
    {
        if ($this->statut === 'non_lu') {
            $this->update([
                'statut' => 'lu',
                'lu_le' => now()
            ]);
        }
    }

    /**
     * Répondre au message
     */
    public function repondre($reponse, $userId = null)
    {
        $this->update([
            'reponse' => $reponse,
            'date_reponse' => now(),
            'statut' => 'repondu',
            'traite_par' => $userId
        ]);
    }
}