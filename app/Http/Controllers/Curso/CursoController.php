<?php

namespace App\Http\Controllers\Curso;

use App\Http\Controllers\Controller;
use App\Models\Cursos\Curso;
use App\Models\Docente\Docente;
use App\Models\Persona\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

use function Pest\Laravel\json;

class CursoController extends Controller
{
    //
    public function listaCursosCompletos()
    {
        $curso = Curso::all();
        return response()->json($curso);
    }

    public function obtnerCursos(Request $request)
    {
        // Obtener el usuario autenticado
        $user = JWTAuth::parseToken()->authenticate();

        // Verificar que el usuario esté autenticado
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Obtener la relación entre el usuario y la persona
        $persona = $user->persona;

        // Verificar que se encontró la persona asociada
        if (!$persona) {
            return response()->json(['message' => 'No se encontró la persona asociada al usuario'], 404);
        }

        // Obtener el id_persona del docente
        $docenteId = $persona->id;

        // Obtener los cursos que enseña el docente usando el id_persona
        $cursos = Docente::where('id_persona', $docenteId)->with('cursos')->get();

        return response()->json([
            'data' => $cursos
        ]);
    }
}
