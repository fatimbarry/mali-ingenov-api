<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentModel extends Model
{
    use HasFactory;
    protected $table = 'departements';


    protected $fillable = ['nom'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
