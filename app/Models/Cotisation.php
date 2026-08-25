<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'membre_id',
        'mois',
        'montant',
        'date_paiement',
        'mode_paiement',
        'statut',
        'commentaire',
        'enregistre_par',
    ];

    protected $casts = [
        'date_paiement' => 'date',
        'montant' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function membre()
    {
        return $this->belongsTo(Membre::class);
    }

    public function enregistrePar()
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }

    public function scopePayee($query)
    {
        return $query->where('statut', 'payee');
    }

    public function scopeMois($query, $mois)
    {
        return $query->where('mois', $mois);
    }
}
