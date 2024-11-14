<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TacheModel extends Model
{
    protected $table = 'taches';
    protected $fillable = [
        'titre',
        'description',
        'temps_previs',
        'status',
        'projet_id',
    ];

    public function projet()
    {
        return $this->belongsTo(ProjetModel::class);
    }

    public function postulations()
    {
        return $this->hasMany(PostulationModel::class);
    }
    use HasFactory;
}
