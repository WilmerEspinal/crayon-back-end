<?php

namespace App\Models\Persona;

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
}
