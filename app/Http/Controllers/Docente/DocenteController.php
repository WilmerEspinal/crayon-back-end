<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Docente\Docente;
use App\Models\Persona\Persona;
use App\Models\User; // Asegúrate de incluir el modelo User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash; // Para encriptar la contraseña

class DocenteController extends Controller
{
    public function registrarDatosDocente(Request $request)
    {
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|min:2|max:50',
            'dni' => 'required|min:8|max:8|unique:persona',
            'email' => 'required|email|unique:users',
            'ap_paterno' => 'required|min:3|max:100',
            'ap_materno' => 'required|min:3|max:100',
            'direccion' => 'min:4|max:150',
            'telefono' => 'min:9|max:9',
            'fecha_nacimiento' => 'required|date',
            'departamento' => 'min:2|max:50',
            'provincia' => 'min:3|max:100',
            'distrito' => 'min:3|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Crear la instancia de Persona
        $persona = new Persona([
            'nombre' => $request->nombre,
            'dni' => $request->dni,
            'ap_paterno' => $request->ap_paterno,
            'ap_materno' => $request->ap_materno,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'departamento' => $request->departamento,
            'provincia' => $request->provincia,
            'distrito' => $request->distrito,
            'email' => $request->email
        ]);
        $persona->save();

        // Crear la instancia de Docente
        $docente = new Docente([
            'id_persona' => $persona->id,
        ]);
        $docente->save();

        // Generar la contraseña automática
        $dni = $request->dni;
        $fechaNacimiento = $request->fecha_nacimiento;
        $dia = date('d', strtotime($fechaNacimiento)); // Obtén el día del mes

        // Obtén las primeras dos letras del nombre y los apellidos
        $primeraLetraNombre = strtoupper(substr($request->nombre, 0, 1));  // Primera letra del nombre
        $primerosDosLetrasAP = strtoupper(substr($request->ap_paterno, 0, 2));
        $primerosDosLetrasAM = strtoupper(substr($request->ap_materno, 0, 2));
        $UltimosDosNumeroDNI = substr($dni, -2);

        $password = $dia . $primeraLetraNombre . $primerosDosLetrasAP . $primerosDosLetrasAM . $UltimosDosNumeroDNI;

        // Crear el usuario en la tabla users
        $user = new User([
            'name' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role_id' => 1,
        ]);
        $user->save();

        return response()->json(['message' => 'Docente registrado exitosamente con usuario creado'], 201);
    }
}
