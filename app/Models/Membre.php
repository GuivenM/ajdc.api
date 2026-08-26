<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

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

    protected $appends = ['nom_complet', 'photo_url', 'role'];

    /**
     * Accesseurs
     */
    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
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
     * Relations
     */
    public function cotisations()
    {
        return $this->hasMany(Cotisation::class);
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