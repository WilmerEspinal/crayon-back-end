<?php

namespace App\Http\Controllers\Persona;

use App\Http\Controllers\Controller;
use App\Models\Alumno\Alumno;
use App\Models\Cuota\Cuota;
use App\Models\Matricula\Matricula;
use App\Models\Persona\Persona;
use App\Models\PadreFamilia\PadreFamilia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class PersonaController extends Controller
{
    public function registarDatosPersona(Request $request)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            // Datos del estudiante
            'nombre' => 'required|min:2|max:50',
            'dni' => 'required|min:8|max:8|unique:persona',
            'ap_paterno' => 'required|min:3|max:100',
            'ap_materno' => 'required|min:3|max:100',
            'direccion' => 'min:4|max:150',
            'telefono' => 'min:9|max:9',
            'fecha_nacimiento' => 'required|date',
            'departamento' => 'required',
            'pais' => 'required|min:3|max:50',
            'provincia' => 'required',
            'distrito' => 'required',
            'email' => 'required|email|unique:users',

            // Datos del padre
            'nombre_padre' => 'required|min:2|max:50',
            'ap_paterno_padre' => 'required|min:3|max:100',
            'ap_materno_padre' => 'required|min:3|max:100',
            'direccion_padre' => 'min:4|max:150',
            'telefono_padre' => 'min:9|max:9',
            'fecha_nacimiento_padre' => 'required|date',
            'relacion_padre' => "required",

            // Datos de la madre
            'nombre_madre' => 'required|min:2|max:50',
            'ap_paterno_madre' => 'required|min:3|max:100',
            'ap_materno_madre' => 'required|min:3|max:100',
            'direccion_madre' => 'min:4|max:150',
            'telefono_madre' => 'min:9|max:9',
            'fecha_nacimiento_madre' => 'required|date',
            'relacion_madre' => 'required',

            // Datos adicionales
            'id_grado' => 'required',
            'situacion' => 'required',
            'costo_matricula' => 'required|numeric',
            'cuota' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Generar la contraseña automática solo para el estudiante
        $dni = $request->dni;
        $fechaNacimiento = $request->fecha_nacimiento;
        $dia = date('d', strtotime($fechaNacimiento)); // Obtén el día del mes

        // Obtén las primeras dos letras del nombre y los apellidos
        $primeraLetraNombre = strtoupper(substr($request->nombre, 0, 1));  // Primera letra del nombre
        $primerosDosLetrasAP = strtoupper(substr($request->ap_paterno, 0, 2));
        $primerosDosLetrasAM = strtoupper(substr($request->ap_materno, 0, 2));
        $UltimosDosNumeroDNI = substr($dni, -2);

        $password = $dia . $primeraLetraNombre . $primerosDosLetrasAP . $primerosDosLetrasAM . $UltimosDosNumeroDNI;



        // Registrar los datos del estudiante
        $datosEstudiante = new Persona([
            'nombre' => $request->nombre,
            'dni' => $request->dni,
            'ap_paterno' => $request->ap_paterno,
            'ap_materno' => $request->ap_materno,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'email' => $request->email
        ]);
        $datosEstudiante->save();

        $alumno = new Alumno([
            'id_persona' => $datosEstudiante->id,
            'id_grado' => $request->id_grado,
            'departamento' => $request->departamento,
            'pais' => $request->pais,
            'provincia' => $request->provincia,
            'distrito' => $request->distrito,
            'situacion' => $request->situacion,
        ]);
        $alumno->save();

        // Registrar la matrícula
        $matricula = new Matricula([
            'id_alumno' => $alumno->id,
            'id_grado' => $request->id_grado,
            'anio' => date('Y'),
            'id_periodo_academico' => 1, // Asigna el periodo académico si es necesario
            'situacion' => $request->situacion,
        ]);
        $matricula->save();

        // Registrar las cuotas
        $cuotas = new Cuota([
            'codigo_matricula' => $matricula->id,
            'id_matricula' => $matricula->id,
            'costo_matricula' => $request->costo_matricula,
            'cuota_1' => $request->cuota,
            'cuota_2' => $request->cuota,
            'cuota_3' => $request->cuota,
            'cuota_4' => $request->cuota,
            'cuota_5' => $request->cuota,
            'cuota_6' => $request->cuota,
            'cuota_7' => $request->cuota,
            'cuota_8' => $request->cuota,
            'cuota_9' => $request->cuota,
            'cuota_10' => $request->cuota,
            // Rellenar las demás cuotas si es necesario
        ]);
        $cuotas->save();

        $matricula->id_cuota = $cuotas->id;
        $matricula->save();

        // Registrar los datos del padre
        $padre = new Persona([
            'nombre' => $request->nombre_padre,
            'dni' => $request->dni_padre,
            'ap_paterno' => $request->ap_paterno_padre,
            'ap_materno' => $request->ap_materno_padre,
            'direccion' => $request->direccion_padre,
            'telefono' => $request->telefono_padre,
            'fecha_nacimiento' => $request->fecha_nacimiento_padre,
            'relacion' => $request->relacion_padre,
        ]);
        $padre->save();

        // Registrar la relación del padre con el alumno
        $padreFamiliaPadre = new PadreFamilia([
            'id_persona' => $padre->id,
            'id_alumno' => $alumno->id,
            'relacion' => $request->relacion_padre,
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
            'relacion' => $request->relacion_madre,
        ]);
        $madre->save();

        // Registrar la relación de la madre con el alumno
        $padreFamiliaMadre = new PadreFamilia([
            'id_persona' => $madre->id,
            'id_alumno' => $alumno->id,
            'relacion' => $request->relacion_madre,
        ]);
        $padreFamiliaMadre->save();


        // Crear el usuario en la tabla users solo para el estudiante
        $user = new User([
            'name' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role_id' => 2,
            'id_persona' => $datosEstudiante->id,

        ]);
        $user->save();

        return response()->json([
            'estudiante' => $datosEstudiante,
            'alumno' => $alumno,
            'padre' => $padre,
            'madre' => $madre,
        ], 201);
    }
}
