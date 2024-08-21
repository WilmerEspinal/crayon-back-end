<?php

namespace App\Http\Controllers\Persona;

use App\Http\Controllers\Controller;
use App\Models\Alumno\Alumno;
use App\Models\Persona\Persona;
use App\Models\PadreFamilia\PadreFamilia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PersonaController extends Controller
{
    public function registarDatosPersona(Request $request)
    {
        // Validar los datos del estudiante y los padres
        $validator = Validator::make($request->all(), [
            // Datos a rellenar del estudiante
            'nombre' => 'required|min:2|max:50',
            'dni' => 'required|min:8|max:8|unique:persona',
            'ap_paterno' => 'required|min:5|max:100',
            'ap_materno' => 'required|min:5|max:100',
            'direccion' => 'min:4|max:150',
            'telefono' => 'min:9|max:9',
            'fecha_nacimiento' => 'required|date',
            'departamento' => 'min:2|max:50',
            'pais' => 'required|min:3|max:50',
            'provincia' => 'min:3|max:100',
            'distrito' => 'min:3|max:100',

            // Datos a rellenar del padre
            'nombre_padre' => 'required|min:2|max:50',
            'ap_paterno_padre' => 'required|min:5|max:100',
            'ap_materno_padre' => 'required|min:5|max:100',
            'direccion_padre' => 'min:4|max:150',
            'telefono_padre' => 'min:9|max:9',
            'fecha_nacimiento_padre' => 'required|date',

            // Datos a rellenar de la madre
            'nombre_madre' => 'required|min:2|max:50',
            'ap_paterno_madre' => 'required|min:5|max:100',
            'ap_materno_madre' => 'required|min:5|max:100',
            'direccion_madre' => 'min:4|max:150',
            'telefono_madre' => 'min:9|max:9',
            'fecha_nacimiento_madre' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Registrar los datos del estudiante
        $datosEstudiante = new Persona([
            'nombre' => $request->nombre,
            'dni' => $request->dni,
            'ap_paterno' => $request->ap_paterno,
            'ap_materno' => $request->ap_materno,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento,
        ]);
        $datosEstudiante->save();

        $alumno = new Alumno([
            'id_persona' => $datosEstudiante->id,
            'departamento' => $request->departamento,
            'pais' => $request->pais,
            'provincia' => $request->provincia,
            'distrito' => $request->distrito,
        ]);
        $alumno->save();

        // Registrar los datos del padre
        $padre = new Persona([
            'nombre' => $request->nombre_padre,
            'dni' => $request->dni_padre,
            'ap_paterno' => $request->ap_paterno_padre,
            'ap_materno' => $request->ap_materno_padre,
            'direccion' => $request->direccion_padre,
            'telefono' => $request->telefono_padre,
            'fecha_nacimiento' => $request->fecha_nacimiento_padre,
        ]);
        $padre->save();

        // Registrar la relación del padre con el alumno
        $padreFamiliaPadre = new PadreFamilia([
            'id_persona' => $padre->id,
            'id_alumno' => $alumno->id,
            'relacion' => 'Padre',
        ]);
        $padreFamiliaPadre->save();

        // Registrar los datos de la madre
        $madre = new Persona([
            'nombre' => $request->nombre_madre,
            'dni' => $request->dni_madre,
            'ap_paterno' => $request->ap_paterno_madre,
            'ap_materno' => $request->ap_materno_madre,
            'direccion' => $request->direccion_madre,
            'telefono' => $request->telefono_madre,
            'fecha_nacimiento' => $request->fecha_nacimiento_madre,
        ]);
        $madre->save();

        // Registrar la relación de la madre con el alumno
        $padreFamiliaMadre = new PadreFamilia([
            'id_persona' => $madre->id,
            'id_alumno' => $alumno->id,
            'relacion' => 'Madre',
        ]);
        $padreFamiliaMadre->save();

        return response()->json([
            'estudiante' => $datosEstudiante,
            'alumno' => $alumno,
            'padre' => $padre,
            'madre' => $madre,
        ], 201);
    }
}
