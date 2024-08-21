<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        /* se crea una variable para alamacenar el valor del metodo Validator::make */
        /* el metodo Validator comprueba los datos que son requeridos */
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);
        /*
        Condicional para verificar si los datos como name, email y password cumplen con los requirimientos
        si falla retorna un error 400
        400->no tiene una estructura válida o contiene caracteres no válidos
        */
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        /*Creamos una nueva instancia del modelo User */
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        //Incripta la contraseña ingresada
        $user->password = bcrypt($request->password);
        /*Inicializamos en true para que el usuario cambie su contraseña depues del registro */
        $user->password_change_required = true;
        //Envia datos al DB
        $user->save();

        //Creacion de un token JWT para el usario registrado
        $token = JWTAuth::fromUser($user);

        // Retorna un json con los datos del user y el token de este usuario
        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'La contraseña es incorrecta'], 400);
        }

        // Obtén el ID del rol asociado al usuario
        $roleId = $user->role_id;

        if ($user->password_change_required) {
            return response()->json([
                'requirePasswordChange' => true,
                'token' => $token,
                'role' => $roleId
            ], 200);
        }

        return response()->json([
            'token' => $token,
            'requirePasswordChange' => false,
            'role' => $roleId
        ]);
    }


    public function changePassword(Request $request)
    {
        //valida que que los capos sean correctos
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        try {
            // Obtener el usuario autenticado desde el token JWT
            $user = JWTAuth::parseToken()->authenticate();

            //valida que la contraseña ingresada coincida con la contraseña almacenada en la base de datos
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['error' => 'La contraseña actual es incorrecta'], 400);
            }

            // Cambiar la contraseña
            $user->password = bcrypt($request->new_password);
            $user->password_change_required = false;
            $user->save();
            //retirna un json con un mesaje si todo fue correcto
            return response()->json(['message' => 'Contraseña cambiada exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No esta autenticado para cambiar la contraseña'], 401);
        }
    }

    public function obtenerUsuario(Request $request)
    {
        try {
            $user = $request->user()->load('rol');

            return response()->json([
                'name' => $user->name,
                'role' => $user->rol ? $user->rol->descripcion : 'Sin rol',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener usuario', 'message' => $e->getMessage()], 500);
        }
    }

    public function validateToken(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json(['valid' => true], 200);
        } catch (JWTException $e) {
            return response()->json(['valid' => false], 401);
        }
    }

    public function serrarSesion(Request $request)
    {
        try {
            // Invalida el token del usuario autenticado
            JWTAuth::invalidate(JWTAuth::parseToken());

            // Retorna un mensaje de éxito
            return response()->json(['message' => 'Sesión cerrada exitosamente'], 200);
        } catch (JWTException $e) {
            // Manejo de errores en caso de que el token no pueda ser invalidado
            return response()->json(['error' => 'No se pudo cerrar la sesión, intente nuevamente'], 500);
        }
    }
}
