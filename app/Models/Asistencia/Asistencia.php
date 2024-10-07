<?php

namespace App\Models\Asistencia;

use App\Models\Alumno\Alumno;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencia';

    // Los atributos que son asignables en masa
    protected $fillable = [
        'id_alumno',
        'id_curso',
        'fecha',
        'estado_asistencia',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno');
    }
}
