<?php

namespace App\Http\Controllers\Asistencia;

use App\Http\Controllers\Controller;
use App\Models\Alumno\Alumno;
use App\Models\Asistencia\Asistencia;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    //
    public function obtnerAlumnoGrado($id_grado)
    {
        $alumnos = Alumno::with(['persona' => function ($query) {
            $query->select('id', 'nombre', 'ap_paterno', 'ap_materno', 'dni');
        }])
            ->where('id_grado', $id_grado)
            ->get(['id', 'id_persona']); // Incluye el id del alumno

        return response()->json($alumnos->map(function ($alumno) {
            return [
                'id' => $alumno->id, // Incluye el id del alumno
                'nombre' => $alumno->persona->nombre,
                'ap_paterno' => $alumno->persona->ap_paterno,
                'ap_materno' => $alumno->persona->ap_materno,
                'dni' => $alumno->persona->dni,
            ];
        }));
    }
    public function registrarAsistencia(Request $request)
    {
        $request->validate([
            'asistencias.*.id_alumno' => 'required|exists:alumno,id',
            'asistencias.*.fecha' => 'required|date',
            'asistencias.*.id_curso' => 'required|exists:curso,id',
            'asistencias.*.estado_asistencia' => 'required|boolean',
        ]);

        $asistencias = $request->input('asistencias');

        foreach ($asistencias as $asistencia) {
            Asistencia::updateOrCreate(
                [
                    'id_alumno' => $asistencia['id_alumno'],
                    'fecha' => $asistencia['fecha'],
                    'id_curso' => $asistencia['id_curso'],
                ],
                [
                    'estado_asistencia' => $asistencia['estado_asistencia'],
                ]
            );
        }

        return response()->json(['message' => 'Asistencia registrada con éxito'], 200);
    }
}
