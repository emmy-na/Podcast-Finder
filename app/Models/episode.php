<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = ['title','description','audio_file','podcast_id'];
    
    // Make sure audio_file is not hidden
    protected $hidden = [];
    
    public function podcast()
    { return $this->belongsTo(Podcast::class); }
}