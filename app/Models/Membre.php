<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membre extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'photo',
        'facebook',
        'instagram',
        'linkedin',
        'twitter',
        'whatsapp',
        'poste',
        'commission',
        'statut'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Accesseurs
     */
    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    public function getRoleAttribute()
    {
        if ($this->poste) {
            return $this->poste;
        }
        if ($this->commission) {
            return 'Commission ' . $this->commission;
        }
        return 'Membre actif';
    }

    /**
     * Scopes
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeBureau($query)
    {
        return $query->whereNotNull('poste');
    }

    public function scopeCommission($query, $commission)
    {
        return $query->where('commission', $commission);
    }
}