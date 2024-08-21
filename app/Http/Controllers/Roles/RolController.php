<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use App\Models\Rol\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    //
    public function obtnerRoles()
    {
        $rol = Rol::all();
        return $rol;
    }
}
