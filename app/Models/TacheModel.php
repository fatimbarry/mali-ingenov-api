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
        'assigned_to',
        'assigned_by',
        'due_date',
    ];

    public function projet()
    {
        return $this->belongsTo(ProjetModel::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function postulations()
    {
        return $this->hasMany(PostulationModel::class);
    }
    use HasFactory;
}
