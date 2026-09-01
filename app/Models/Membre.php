<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Authenticatable (et non Model) : un Membre peut désormais posséder des
// tokens Sanctum et se connecter à l'espace membre, séparément des comptes
// `User` (admin/super_admin/moderateur) qui gardent leur propre espace.
class Membre extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'adhesion_id',
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
        'statut',
        'email',
        'password',
        'email_verified_at',
        'derniere_connexion',
        'activation_token',
        'activation_token_expire_at',
    ];

    protected $hidden = [
        'password',
        'activation_token',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'derniere_connexion' => 'datetime',
        'activation_token_expire_at' => 'datetime',
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
    public function adhesion()
    {
        return $this->belongsTo(Adhesion::class);
    }

    public function cotisations()
    {
        return $this->hasMany(Cotisation::class);
    }

    public function evenements()
    {
        return $this->belongsToMany(Evenement::class, 'participations')
                    ->withPivot('statut', 'date_inscription', 'commentaire')
                    ->withTimestamps();
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