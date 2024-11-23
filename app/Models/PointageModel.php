<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointageModel extends Model
{
    protected $table = 'pointages';
    protected $fillable = [
        'users_id',
        'status',
        'date',
        'heure_normales',
        'heure_supplementaires',
        'punch_in',
        'punch_out',
        'ecart'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pointage) {
            if (is_null($pointage->punch_out)) {
                $pointage->punch_out = '00:00:00'; // ou null selon votre choix
            }
        });
    }


    public function employee()
    {
        return $this->belongsTo(User::class);
    }

    use HasFactory;
}
