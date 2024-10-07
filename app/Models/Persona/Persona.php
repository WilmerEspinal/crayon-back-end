<?php

namespace App\Models\Persona;

use App\Models\Alumno\Alumno;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;
    protected $table = 'persona';

    protected $fillable = [
        'nombre',
        'dni',
        'ap_paterno',
        'ap_materno',
        'direccion',
        'telefono',
        'email',
        'fecha_nacimiento'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id_persona', 'id');
    }
    public function alumno()
    {
        return $this->hasOne(Alumno::class, 'id_persona', 'id');
    }
}
