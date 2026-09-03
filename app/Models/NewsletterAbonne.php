<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterAbonne extends Model
{
    use HasFactory;

    protected $table = 'newsletter_abonnes';

    protected $fillable = [
        'email',
        'statut',
        'source',
        'desinscrit_le',
    ];

    protected $casts = [
        'desinscrit_le' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scopes
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }
}
