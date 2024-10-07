<?php

namespace App\Models\Alumno;

use App\Models\Cuota\Cuota;
use App\Models\Grado\Grado;
use App\Models\Matricula\Matricula;
use App\Models\PadreFamilia\PadreFamilia;
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
        'id_persona',
        'departamento',
        'pais',
        'provincia',
        'distrito',
        'id_grado',
        'id_seccion',
        'estado_pago'
    ];

    // Define la relación con el modelo Persona
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }



    // Relación con el modelo Grado
    public function grado()
    {
        return $this->belongsTo(Grado::class, 'id_grado');
    }

    public function matriculas()
    {
        return $this->hasOne(Matricula::class, 'id_alumno', 'id');
    }


    public function cuotas()
    {
        return $this->hasManyThrough(Cuota::class, Matricula::class);
    }
    public function familia()
    {
        return $this->hasMany(PadreFamilia::class, 'id_alumno');
    }
}
