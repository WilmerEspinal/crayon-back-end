<?php

namespace App\Http\Controllers\PadreFamilia;

use App\Http\Controllers\Controller;
use App\Models\PadreFamilia\PadreFamilia;
use App\Models\Persona\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PadreFamiliaController extends Controller
{
    //
    public function registrarPadres(Request $request, $id_alumno)
    {
        // Validar los datos entrantes


        $validator = Validator::make($request->all(), [
            'nombre_padre' => 'required|string|max:255',
            'dni_padre' => 'required|integer|unique:personas,dni',
            'ap_paterno_padre' => 'required|string|max:255',
            'ap_materno_padre' => 'required|string|max:255',
            'direccion_padre' => 'required|string|max:255',
            'telefono_padre' => 'required|string|max:20',
            'email_padre' => 'nullable|email|max:255',
            'fecha_nacimiento_padre' => 'required|date',

            'nombre_madre' => 'required|string|max:255',
            'dni_madre' => 'required|integer|unique:personas,dni',
            'ap_paterno_madre' => 'required|string|max:255',
            'ap_materno_madre' => 'required|string|max:255',
            'direccion_madre' => 'required|string|max:255',
            'telefono_madre' => 'required|string|max:20',
            'email_madre' => 'nullable|email|max:255',
            'fecha_nacimiento_madre' => 'required|date',
        ]);


        $padre = new Persona([
            'nombre' => $request->input('nombre_padre'),
            'dni' => $request->input('dni_padre'),
            'ap_paterno' => $request->input('ap_paterno_padre'),
            'ap_materno' => $request->input('ap_materno_padre'),
            'direccion' => $request->input('direccion_padre'),
            'telefono' => $request->input('telefono_padre'),
            'email' => $request->input('email_padre'),
            'fecha_nacimiento' => $request->input('fecha_nacimiento_padre'),
        ]);
        $padre->save();


        $madre = new Persona([
            'nombre' => $request->input('nombre_madre'),
            'dni' => $request->input('dni_madre'),
            'ap_paterno' => $request->input('ap_paterno_madre'),
            'ap_materno' => $request->input('ap_materno_madre'),
            'direccion' => $request->input('direccion_madre'),
            'telefono' => $request->input('telefono_madre'),
            'email' => $request->input('email_madre'),
            'fecha_nacimiento' => $request->input('fecha_nacimiento_madre'),
        ]);
        $madre->save();
        $padreFamiliaPadre = new PadreFamilia([
            'id_persona' => $padre->id,
            'id_alumno' => $id_alumno,
            'relacion' => 'Padre',
        ]);
        $padreFamiliaPadre->save();

        $padreFamiliaMadre = new PadreFamilia([
            'id_persona' => $madre->id,
            'id_alumno' => $id_alumno,
            'relacion' => 'Madre',
        ]);
        $padreFamiliaMadre->save();

        return response()->json(['message' => 'Datos de los padres registrados correctamente'], 201);
    }
}
