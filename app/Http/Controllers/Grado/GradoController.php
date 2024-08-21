<?php

namespace App\Http\Controllers\Grado;

use App\Http\Controllers\Controller;
use App\Models\Grado\Grado;
use Illuminate\Http\Request;

class GradoController extends Controller
{
    //
    public function obtnerGrado()
    {
        $grado = Grado::all();
        return response()->json($grado);
    }
}
