<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjetModel extends Model
{

    protected $table = 'projets';
    protected $fillable = [

        'libelle',
        'description',
        'statut',
        'date_debut',
        'date_fin',
        'delai',
        'client_id',

    ];

    // Relation avec le modèle Contract
    public function contracts()
    {
        return $this->hasMany(Contrat::class);
    }

    public function client()
    {
        return $this->belongsTo(ClientModel::class, 'client_id');
    }

    protected $attributes = [
        'statut' => 'en cours',
    ];


    public function taches()
    {
        return $this->hasMany(TacheModel::class);
    }



    use HasFactory;
}
