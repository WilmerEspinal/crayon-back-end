<?php

namespace App\Models\Matricula;

use App\Models\Alumno\Alumno;
use App\Models\Cuota\Cuota;
use App\Models\Grado\Grado;
use App\Models\Persona\Persona;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class Matricula extends Model
{
    use HasFactory;
    protected $table = 'matricula';

    protected $fillable = [
        'id_alumno',
        'id_grado',
        'id_seccion',
        'anio',
        'id_cuota',
        'id_requisitos',
        'situacion',
    ];
    // Relación con el modelo Alumno
    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno');
    }

    // Relación con el modelo Grado
    public function grado()
    {
        return $this->belongsTo(Grado::class, 'id_grado');
    }

    // Relación con el modelo Cuota
    public function cuota()
    {
        return $this->hasMany(Cuota::class, 'id_matricula', 'id');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id'); // Ajusta según tu estructura
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id_persona', 'id');
    }
}
