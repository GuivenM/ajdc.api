<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuideSousSection extends Model
{
    use HasFactory;

    protected $table = 'guide_sous_sections';

    protected $fillable = [
        'section_id',
        'titre',
        'contenu',
        'image',
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
    public function section()
    {
        return $this->belongsTo(GuideSection::class, 'section_id');
    }

    public function documents()
    {
        return $this->hasMany(GuideDocument::class, 'sous_section_id');
    }

    /**
     * Scopes
     */
    public function scopePublie($query)
    {
        return $query->where('statut', 'publie');
    }

    protected $appends = ['image_url'];

    /**
     * Accesseurs
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }
}