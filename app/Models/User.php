<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sexe',
        'prenom',
        'nom',
        'date_Emb',
        'email',
        'password',
        'photo',
        'role',
        'post',
        'department_id',
        'assigned_to',
        'assigned_by',

    ];

    public function department()
    {
        return $this->belongsTo(DepartmentModel::class);
    }

    public function punches()
    {
        return $this->hasMany(PointageModel::class, 'users_id'); // Colonne correcte dans la table pointages
    }

    public function postulations()
    {
        return $this->hasMany(PostulationModel::class);
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
