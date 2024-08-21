<?php

namespace App\Models\Rol;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'descripcion',
    ];

    // Define la relación inversa con el modelo User
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public $timestamps = false;
}
