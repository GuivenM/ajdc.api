<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'photo',
        'role',
        'telephone',
        'est_actif',
        'email_verified_at',
        'derniere_connexion',
        'membre_id',
        'activation_token',
        'activation_token_expire_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'activation_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'derniere_connexion' => 'datetime',
        'activation_token_expire_at' => 'datetime',
        'est_actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = ['nom_complet', 'photo_url', 'initiales', 'role_label'];

    /**
     * Accesseurs - NE LES DECLAREZ QU'UNE SEULE FOIS
     */
    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }

    public function getInitialesAttribute()
    {
        return strtoupper(substr($this->prenom, 0, 1) . substr($this->nom, 0, 1));
    }

    public function getRoleLabelAttribute()
    {
        $roles = [
            'super_admin' => 'Super Administrateur',
            'admin' => 'Administrateur',
            'moderateur' => 'Modérateur',
            'tresorier' => 'Trésorier',
        ];

        return $roles[$this->role] ?? $this->role;
    }

    /**
     * Vérifications de rôle
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isModerateur()
    {
        return in_array($this->role, ['super_admin', 'admin', 'moderateur']);
    }

    /**
     * Relations
     */
    public function membre()
    {
        return $this->belongsTo(Membre::class);
    }
}