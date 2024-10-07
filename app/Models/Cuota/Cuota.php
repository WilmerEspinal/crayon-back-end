<?php

namespace App\Models\Cuota;

use App\Models\Alumno\Alumno;
use App\Models\Matricula\matricula;
use App\Models\Persona\Persona;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    use HasFactory;
    protected $table = 'cuota';

    protected $fillable = [
        'codigo_matricula',
        'id_matricula',
        'cuota_1',
        'cuota_2',
        'cuota_3',
        'cuota_4',
        'cuota_5',
        'cuota_6',
        'cuota_7',
        'cuota_8',
        'cuota_9',
        'cuota_10',
        'costo_matricula',
        'c1_estado',
        'c2_estado',
        'c3_estado',
        'c4_estado',
        'c5_estado',
        'c6_estado',
        'c7_estado',
        'c8_estado',
        'c9_estado',
        'c10_estado',
    ];


    protected $estado = [
        'c1_estado' => 'boolean',
        'c2_estado' => 'boolean',
        'c3_estado' => 'boolean',
        'c4_estado' => 'boolean',
        'c5_estado' => 'boolean',
        'c6_estado' => 'boolean',
        'c7_estado' => 'boolean',
        'c8_estado' => 'boolean',
        'c9_estado' => 'boolean',
        'c10_estado' => 'boolean',
        'matricula_estado' => 'boolean',
    ];

    public function matricula()
    {
        return $this->belongsTo(matricula::class, 'id_matricula');
    }

    // Relación lógica con la tabla Persona
    public function persona()
    {
        return $this->hasOneThrough(
            Persona::class,  // Modelo final: Persona
            Alumno::class,   // Modelo intermedio: Alumno
            'id',            // Clave primaria en Alumno (es el campo id)
            'id_persona',    // Clave primaria en Persona
            'id_matricula',  // Llave foránea en Cuota que apunta a Matricula
            'id'             // Llave en Alumno que apunta a Persona (es el campo id)
        );
    }
}
