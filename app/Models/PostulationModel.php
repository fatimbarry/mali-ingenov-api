<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostulationModel extends Model
{
    protected $table = 'postules';

    protected $fillable = [
        'user_id',
        'tache_id',
        'statut',
        'date_postulation',
        'date_decision',
    ];
    use HasFactory;
}
