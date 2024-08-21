<?php

namespace App\Models\Alumno;

use App\Models\Persona\Persona;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;

    // Especifica el nombre de la tabla en la base de datos
    protected $table = 'alumno';

    // Los atributos que son asignables en masa
    protected $fillable = [
        'id_persona', 'departamento', 'pais', 'provincia', 'distrito'
    ];

    // Define la relación con el modelo Persona
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }
}
