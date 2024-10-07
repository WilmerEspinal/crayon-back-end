<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Models\Alumno\Alumno;
use App\Models\Matricula\Matricula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AlumnoController extends Controller
{
    public function obtenerAlumnosConFamiliaPorGrado($id_grado)
    {
        $alumnos = Alumno::with(['persona' => function ($query) {
            $query->select('id', 'nombre', 'ap_paterno', 'ap_materno', 'dni', 'direccion', 'telefono', 'fecha_nacimiento');
        }, 'familia' => function ($query) {
            $query->with(['persona' => function ($query) {
                $query->select('id', 'nombre', 'ap_paterno', 'ap_materno', 'dni');
            }])->select('id', 'id_persona', 'relacion', 'id_alumno');
        }])
            ->where('id_grado', $id_grado)
            ->get(['id', 'id_persona']);

        return response()->json($alumnos->map(function ($alumno) {
            return [
                'id' => $alumno->id,
                'nombre' => $alumno->persona->nombre,
                'ap_paterno' => $alumno->persona->ap_paterno,
                'ap_materno' => $alumno->persona->ap_materno,
                'dni' => $alumno->persona->dni,
                'direccion' => $alumno->persona->direccion,
                'telefono' => $alumno->persona->telefono,
                'fecha_nacimiento' => $alumno->persona->fecha_nacimiento,
                'familia' => $alumno->familia->map(function ($familiar) {
                    return [
                        'nombre' => $familiar->persona->nombre,
                        'ap_paterno' => $familiar->persona->ap_paterno,
                        'ap_materno' => $familiar->persona->ap_materno,
                        'dni' => $familiar->persona->dni,
                        'relacion' => $familiar->relacion,
                    ];
                }),
            ];
        }));
    }

    public function editarAlumnoYFamilia(Request $request, $id)
    {
        // Encontrar el alumno por ID con sus relaciones
        $alumno = Alumno::with(['persona', 'familia'])->findOrFail($id);

        // Validar los datos de entrada para el alumno, incluyendo el grado
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'ap_paterno' => 'required|string|max:255',
            'ap_materno' => 'nullable|string|max:255',
            'dni' => 'min:8|max:8|unique:persona,dni,' . $alumno->persona->id,
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:15',
            'fecha_nacimiento' => 'nullable|date',
            'grado' => 'required|integer|exists:grado,id', // Validar que el ID del grado exista en la tabla 'grados'
            'familia' => 'required|array',
            'familia.*.id' => 'required|integer|exists:padre_familia,id',
            'familia.*.nombre' => 'required|string|max:255',
            'familia.*.ap_paterno' => 'required|string|max:255',
            'familia.*.ap_materno' => 'nullable|string|max:255',
            'familia.*.dni' => 'required|max:8|min:8',
            'familia.*.relacion' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Actualizar la información del alumno
        $alumno->persona->nombre = $request->nombre;
        $alumno->persona->ap_paterno = $request->ap_paterno;
        $alumno->persona->ap_materno = $request->ap_materno;
        $alumno->persona->dni = $request->dni;
        $alumno->persona->direccion = $request->direccion;
        $alumno->persona->telefono = $request->telefono;
        $alumno->persona->fecha_nacimiento = $request->fecha_nacimiento;

        // Guardar los cambios del alumno
        $alumno->persona->save();
        $alumno->save();

        // Actualizar el grado del alumno en la tabla 'alumno'
        $alumno->id_grado = $request->grado;
        $alumno->save();

        // Actualizar la matrícula del alumno con el nuevo grado
        $matricula = Matricula::where('id_alumno', $alumno->id)->first();
        if ($matricula) {
            $matricula->id_grado = $request->grado; // Actualizar el ID del grado en la tabla 'matricula'
            $matricula->save();
        }

        // Actualizar la información de los familiares
        foreach ($request->familia as $familiarData) {
            $familiar = $alumno->familia->firstWhere('id', $familiarData['id']);

            if ($familiar) {
                $familiar->persona->nombre = $familiarData['nombre'];
                $familiar->persona->ap_paterno = $familiarData['ap_paterno'];
                $familiar->persona->ap_materno = $familiarData['ap_materno'];
                $familiar->persona->dni = $familiarData['dni'];
                $familiar->relacion = $familiarData['relacion'];
                $familiar->persona->save();
                $familiar->save();
            }
        }

        return response()->json(['message' => 'Alumno, grado y familiares actualizados correctamente.']);
    }




    public function obtenerAlumnoPorIdConPadre($id)
    {
        $alumno = Alumno::with(['persona', 'familia.persona'])
            ->findOrFail($id);

        return response()->json([
            'id' => $alumno->id,
            'nombre' => $alumno->persona->nombre,
            'ap_paterno' => $alumno->persona->ap_paterno,
            'ap_materno' => $alumno->persona->ap_materno,
            'dni' => $alumno->persona->dni,
            'direccion' => $alumno->persona->direccion,
            'telefono' => $alumno->persona->telefono,
            'grado' => $alumno->id_grado,
            'fecha_nacimiento' => $alumno->persona->fecha_nacimiento,
            'familia' => $alumno->familia->map(function ($familiar) {
                return [
                    'id' => $familiar->id, // Agregar el ID del familiar
                    'nombre' => $familiar->persona->nombre,
                    'ap_paterno' => $familiar->persona->ap_paterno,
                    'ap_materno' => $familiar->persona->ap_materno,
                    'dni' => $familiar->persona->dni,
                    'relacion' => $familiar->relacion,
                ];
            }),
        ]);
    }
}
