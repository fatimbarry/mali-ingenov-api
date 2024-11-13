<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientModel extends Model
{

    protected $table = 'clients';
    protected $fillable =
        [
            'nom',
            'prenom',
            'email',
            'telephone'
        ];

    public function factures()
    {
        return $this->hasMany(FactureModel::class);

    }


    public function projets()
    {
        return $this->hasMany(ProjetModel::class, 'client_id')
            ->where('archived', 0);
    }
    use HasFactory;
}
