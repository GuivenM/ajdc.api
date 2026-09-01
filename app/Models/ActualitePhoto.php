<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ActualitePhoto extends Model
{
    protected $fillable = ['actualite_id', 'chemin', 'ordre'];

    protected $appends = ['url'];

    public function actualite()
    {
        return $this->belongsTo(Actualite::class);
    }

    public function getUrlAttribute()
    {
        return $this->chemin ? Storage::disk('public')->url($this->chemin) : null;
    }
}
