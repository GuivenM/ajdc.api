<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'membre_id',
        'evenement_id',
        'mois',
        'mois_liste',
        'nom_payeur',
        'telephone_payeur',
        'email_payeur',
        'montant',
        'devise',
        'statut',
        'fedapay_transaction_id',
        'fedapay_reference',
        'fedapay_derniere_reponse',
    ];

    protected $casts = [
        'mois_liste' => 'array',
        'montant' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function membre()
    {
        return $this->belongsTo(Membre::class);
    }

    public function evenement()
    {
        return $this->belongsTo(Evenement::class);
    }

    public function scopeReussi($query)
    {
        return $query->where('statut', 'reussi');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
