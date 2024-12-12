<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    use HasFactory;
    protected $fillable = [
        'titre',
        'description',
        'type_contrat',
        'statut',
        'montant',
        'date',
        'projet_id',
    ];

    // Relation avec la table projets
    public function projet()
    {
        return $this->belongsTo(ProjetModel::class);
    }
}
