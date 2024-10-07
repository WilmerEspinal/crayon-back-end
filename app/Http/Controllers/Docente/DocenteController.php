<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Docente\Docente;
use App\Models\Persona\Persona;
use App\Models\User; // Asegúrate de incluir el modelo User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash; // Para encriptar la contraseña

use function PHPSTORM_META\map;

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
            'cursos' => 'required|array', // Validamos que venga un array de IDs de cursos
            'cursos.*' => 'exists:curso,id', // Verificamos que cada curso exista en la tabla curso
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

        foreach ($request->cursos as $cursoId) {
            $docente = new Docente([
                'id_persona' => $persona->id,
                'id_curso' => $cursoId // Save the course ID directly in the Docente model
            ]);
            $docente->save();
        }


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
            'id_persona' => $persona->id,
        ]);
        $user->save();



        return response()->json(['message' => 'Docente registrado exitosamente con usuario creado'], 201);
    }

    public function listaDocentes(Request $request)
    {
        $listaDocentes = Docente::with('persona:id,nombre,dni,ap_paterno,ap_materno,direccion,telefono,email', 'cursos:id,descripcion')->get();

        $datosDocente = [];

        foreach ($listaDocentes as $docente) {
            if ($docente->persona) { // Ensure persona is not null
                $index = array_search($docente->persona->dni, array_column($datosDocente, 'dni'));
                if ($index === false) {
                    $datosDocente[] = [
                        "id" => $docente->persona->id,
                        "nombre" => $docente->persona->nombre,
                        "apellidos" => $docente->persona->ap_paterno . " " . $docente->persona->ap_materno,
                        "dni" => $docente->persona->dni,
                        "telefono" => $docente->persona->telefono,
                        "direccion" => $docente->persona->direccion,
                        'cursos' => $docente->cursos->pluck('descripcion')->toArray(),
                    ];
                } else {
                    $datosDocente[$index]['cursos'] = array_unique(array_merge($datosDocente[$index]['cursos'], $docente->cursos->pluck('descripcion')->toArray()));
                }
            }
        }

        return response()->json($datosDocente);
    }

    public function actualizarDatosDocente(Request $request, $id)
    {
        // Validar los datos entrantes
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|min:2|max:50',
            'dni' => 'sometimes|required|min:8|max:8|unique:persona,dni,' . $id . ',id', // Update to use 'id' for uniqueness check
            'ap_paterno' => 'sometimes|required|min:3|max:100',
            'ap_materno' => 'sometimes|required|min:3|max:100',
            'direccion' => 'sometimes|min:4|max:150',
            'telefono' => 'nullable|min:9|max:9',
            // 'fecha_nacimiento' => 'sometimes|required|date',
            'fecha_nacimiento' => 'nullable|date',
            'cursos' => 'sometimes|required|array',
            'cursos.*' => 'exists:curso,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Buscar la persona asociada al docente usando el ID
        $persona = Persona::find($id); // Change to find by id

        // Verificar si la persona existe
        if (!$persona) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        // Actualizar los datos de la persona
        $persona->update([
            'nombre' => $request->nombre ?? $persona->nombre,
            'dni' => $request->dni ?? $persona->dni,
            'ap_paterno' => $request->ap_paterno,
            'ap_materno' => $request->ap_materno,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'fecha_nacimiento' => $request->fecha_nacimiento,
        ]);

        // Actualizar los cursos del docente
        // Eliminar los registros existentes para el id_persona
        Docente::where('id_persona', $persona->id)->delete();

        // Insertar los nuevos cursos
        foreach ($request->cursos as $cursoId) {
            Docente::create([
                'id_persona' => $persona->id,
                'id_curso' => $cursoId,
            ]);
        }

        // Actualizar el nombre de usuario en la tabla users
        $user = User::where('id_persona', $persona->id)->first();

        if ($user) {
            $user->update([
                'name' => $request->nombre, // Actualizar el nombre del usuario
            ]);
        }

        return response()->json(['message' => 'Docente actualizado exitosamente'], 200);
    }


    public function obtenerDocente($id)
    {
        // Fetch all docentes with their related persona and cursos
        $listaDocentes = Docente::with('persona:id,nombre,dni,ap_paterno,ap_materno,direccion,telefono,fecha_nacimiento', 'cursos:id,descripcion')->get();

        $datosDocente = [];

        foreach ($listaDocentes as $docente) {
            // Check if the docente's persona id matches the provided id
            if ($docente->id_persona == $id) {
                // Check if the docente is already in the datosDocente array
                $index = array_search($docente->persona->dni, array_column($datosDocente, 'dni'));

                if ($index === false) {
                    // If it does not exist, add a new record for the docente
                    $datosDocente[] = [
                        "nombre" => $docente->persona->nombre,
                        "ap_paterno" => $docente->persona->ap_paterno,
                        "ap_materno" => $docente->persona->ap_materno,
                        "dni" => $docente->persona->dni,
                        "telefono" => $docente->persona->telefono,
                        "direccion" => $docente->persona->direccion,
                        "fecha_nacimiento" => $docente->persona->fecha_nacimiento,
                        'cursos' => $docente->cursos->pluck('descripcion')->toArray(), // Get courses
                    ];
                } else {
                    // If it already exists, combine the courses without duplicates
                    $datosDocente[$index]['cursos'] = array_unique(array_merge($datosDocente[$index]['cursos'], $docente->cursos->pluck('descripcion')->toArray()));
                }
            }
        }

        if (empty($datosDocente)) {
            return response()->json(['message' => 'Docente no encontrado'], 404); // Handle not found case
        }

        return response()->json($datosDocente);
    }




































    public function eliminarDocente($id)
    {
        // Buscar la persona asociada al docente
        $persona = Persona::find($id);

        // Verificar si la persona existe
        if (!$persona) {
            return response()->json(['message' => 'Docente no encontrado'], 404);
        }

        // Eliminar los registros de Docente asociados a la persona
        Docente::where('id_persona', $persona->id)->delete();

        // Eliminar el usuario asociado a la persona
        $user = User::where('id_persona', $persona->id)->first();
        if ($user) {
            $user->delete();
        }

        // Eliminar la persona
        $persona->delete();

        return response()->json(['message' => 'Docente eliminado exitosamente'], 200);
    }
}
