<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuideSection extends Model
{
    use HasFactory;

    protected $table = 'guide_sections';

    protected $fillable = [
        'titre',
        'description',
        'categorie',
        'contenu',
        'image',
        'icone',
        'ordre',
        'statut'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relations
     */
    public function sousSections()
    {
        return $this->hasMany(GuideSousSection::class, 'section_id')->orderBy('ordre');
    }

    /**
     * Scopes
     */
    public function scopePublie($query)
    {
        return $query->where('statut', 'publie');
    }

    public function scopeByCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    protected $appends = ['image_url', 'icone_url'];

    /**
     * Accesseurs
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function getIconeUrlAttribute()
    {
        return $this->icone ? Storage::disk('public')->url($this->icone) : null;
    }
}