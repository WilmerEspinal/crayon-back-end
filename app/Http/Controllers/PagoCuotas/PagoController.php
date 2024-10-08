<?php

namespace App\Http\Controllers\PagoCuotas;

use App\Http\Controllers\Controller;
use App\Models\Alumno\Alumno;
use App\Models\Cuota\Cuota; // Asegúrate de que la ruta del modelo sea correcta
use App\Models\Matricula\Matricula;
use App\Models\Persona\Persona;
use Exception;
use Illuminate\Http\Request;
use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;
use Illuminate\Support\Facades\Log;
use MercadoPago\Payer;
use Tymon\JWTAuth\Facades\JWTAuth;



use MercadoPago\Payment;

use function Pest\Laravel\json;

class PagoController extends Controller
{
    public function pagarCuota(Request $request, $cuotaNumero)
    {
        // Autenticación del usuario
        $user = JWTAuth::parseToken()->authenticate();

        // Obtener la persona asociada al usuario autenticado
        $persona = Persona::where('id', $user->id_persona)->first();

        if (!$persona) {
            return response()->json(['message' => 'No se encontró una persona asociada al usuario.'], 404);
        }

        // Obtener el alumno asociado a la persona
        $alumno = Alumno::where('id_persona', $persona->id)->first();

        if (!$alumno) {
            return response()->json(['message' => 'No se encontró un alumno asociado a la persona.'], 404);
        }

        // Buscar la matrícula del alumno
        $matricula = Matricula::where('id_alumno', $alumno->id)->first();

        if (!$matricula) {
            return response()->json(['message' => 'No se encontró una matrícula para el alumno.'], 404);
        }

        // Buscar la cuota asociada a la matrícula
        $cuota = Cuota::where('id_matricula', $matricula->id)->first();

        if (!$cuota) {
            return response()->json(['message' => 'No se encontraron cuotas para la matrícula.'], 404);
        }

        // Validación del número de cuota
        if ($cuotaNumero < 1 || $cuotaNumero > 10) {
            return response()->json(['message' => 'Número de cuota no válido.'], 400);
        }

        $cuotaAPagar = $cuota->{"cuota_$cuotaNumero"};
        $cuotaEstado = "c{$cuotaNumero}_estado";

        if (!$cuotaAPagar) {
            return response()->json(['message' => 'La cuota especificada ya está pagada o no existe.'], 400);
        }

        $emailPersona = $persona->email ?? null;

        if (!$emailPersona) {
            return response()->json(['message' => 'No se encontró un correo electrónico asociado a esta persona.'], 400);
        }

        \MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));

        $preference = new Preference();

        $item = new Item();
        $item->title = "Pago de cuota $cuotaNumero";
        $item->quantity = 1;
        $item->unit_price = (float)$cuotaAPagar;
        $preference->items = [$item];

        $payer = new Payer();
        $payer->email = $emailPersona;

        $preference->payer = $payer;

        $preference->external_reference = $cuota->id;

        $preference->back_urls = [
            "success" => url("/api/payment-success/{$cuota->id}/$cuotaEstado"),
            "failure" => url("/api/payment-failure"),
            "pending" => url("/api/payment-pending"),
        ];

        $preference->auto_return = "approved";

        $preference->save();

        return response()->json(['init_point' => $preference->init_point]);
    }




    public function pagoRealizado(Request $request, $id, $cuotaEstado)
    {
        \MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));

        $payment_id = $request->query('payment_id');

        if (!$payment_id) {
            return response()->json(['message' => 'ID de pago no proporcionado.'], 400);
        }

        try {
            $payment = Payment::find_by_id($payment_id);

            if ($payment && $payment->status == 'approved') {
                $cuota = Cuota::findOrFail($id);
                $cuota->{$cuotaEstado} = true;


                $cuota->{"cuota_" . substr($cuotaEstado, 1, 1)} = 0;

                $cuota->save();

                return response()->json(['message' => 'Pago realizado con éxito.']);
            } else {
                return response()->json(['message' => 'El pago no fue aprobado o no se encontró.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al verificar el pago: ' . $e->getMessage()], 500);
        }
    }

    public function notificacionPagos(Request $request)
    {
        Log::info(json_encode($request->all()));
    }





    public function paymentSuccess(Request $request, $id, $cuotaEstado)
    {
        \MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));

        $payment_id = $request->query('payment_id');

        if (!$payment_id) {
            return response()->json(['message' => 'ID de pago no proporcionado.'], 400);
        }

        try {
            $payment = Payment::find_by_id($payment_id);

            if ($payment && $payment->status == 'approved') {
                $cuota = Cuota::findOrFail($id);
                $cuota->{$cuotaEstado} = true;

                // Vaciar la cuota pagada
                preg_match('/c(\d+)_estado/', $cuotaEstado, $matches);
                $cuotaNumero = $matches[1];
                $cuota->{"cuota_$cuotaNumero"} = 0;


                $cuota->save();

                return response()->json(['message' => 'Pago realizado con éxito.']);
            } else {
                return response()->json(['message' => 'El pago no fue aprobado o no se encontró.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al verificar el pago: ' . $e->getMessage()], 500);
        }
    }

    public function pagoFallido()
    {
        return response()->json(['message' => 'El pago ha fallado.']);
    }

    public function pagoPendiente()
    {
        return response()->json(['message' => 'El pago está pendiente.']);
    }



    public function obtenerCuotasAlumno()
    {
        // Obtener el usuario autenticado desde el token JWT
        $usuarioAutenticado = JWTAuth::parseToken()->authenticate();

        // Verificar si el usuario fue encontrado
        if (!$usuarioAutenticado) {
            return response()->json(['message' => 'Usuario no autenticado o no encontrado.'], 401);
        }

        // Log para verificar el usuario autenticado
        Log::info('Usuario autenticado:', ['user_id' => $usuarioAutenticado->id]);

        // Verificar si el usuario tiene una persona asociada
        $persona = $usuarioAutenticado->persona;

        if (!$persona) {
            Log::info('No se encontró persona asociada.', ['user_id' => $usuarioAutenticado->id]);
            return response()->json(['message' => 'No se encontró una persona asociada al usuario autenticado.', 'user_id' => $usuarioAutenticado->id], 404);
        }

        // Log para verificar la persona asociada
        Log::info('Persona encontrada:', ['persona_id' => $persona->id]);

        // Verificar si la persona tiene un alumno asociado
        $alumno = $persona->alumno;

        if (!$alumno) {
            return response()->json(['message' => 'No se encontró un alumno asociado a esta persona.'], 404);
        }

        // Obtener solo las cuotas no pagadas asociadas al alumno
        $cuotasSinPagar = Cuota::whereHas('matricula.alumno', function ($query) use ($alumno) {
            $query->where('id', $alumno->id);
        })->with('matricula.alumno.persona')->get();

        // Crear un array para las cuotas no pagadas
        $cuotasNoPagadas = [];

        // Revisar el estado de cada cuota
        foreach ($cuotasSinPagar as $cuota) {
            for ($i = 1; $i <= 10; $i++) {
                $estadoKey = "c{$i}_estado";

                if ($cuota->$estadoKey == 0) {
                    $cuotasNoPagadas[] = [
                        'dni_alumno' => $cuota->matricula->alumno->persona->dni,
                        'cuota' => $cuota->{'cuota_' . $i},
                        'cuota_nombre' => 'Cuota ' . $i,
                        'estado' => $cuota->$estadoKey,
                        'costo_matricula' => $cuota->costo_matricula,
                        'matricula_estado' => $cuota->matricula_estado,
                    ];
                }
            }
        }

        // Verificar si hay cuotas no pagadas
        if (empty($cuotasNoPagadas)) {
            return response()->json(['message' => 'No hay cuotas sin pagar.'], 404);
        }

        // Devolver las cuotas no pagadas asociadas al alumno en formato JSON
        return response()->json($cuotasNoPagadas);
    }



    public function obtenerCuotasPagadasAlumno()
    {
        // Obtener el usuario autenticado desde el token JWT
        $usuarioAutenticado = JWTAuth::parseToken()->authenticate();

        // Verificar si el usuario fue encontrado
        if (!$usuarioAutenticado) {
            return response()->json(['message' => 'Usuario no autenticado o no encontrado.'], 401);
        }

        // Obtener la persona asociada al usuario autenticado
        $persona = $usuarioAutenticado->persona;

        // Verificar si la persona tiene un alumno asociado
        if (!$persona) {
            return response()->json(['message' => 'No se encontró una persona asociada al usuario autenticado.'], 404);
        }

        // Obtener el alumno asociado a la persona
        $alumno = $persona->alumno;

        // Verificar si se encontró un alumno
        if (!$alumno) {
            return response()->json(['message' => 'No se encontró un alumno asociado a esta persona.'], 404);
        }

        // Obtener las cuotas pagadas asociadas al alumno utilizando whereHas
        $cuotas = Cuota::whereHas('matricula.alumno', function ($query) use ($alumno) {
            $query->where('id', $alumno->id);
        })->with('matricula.alumno.persona')->get();

        // Verificar si se encontraron cuotas
        if ($cuotas->isEmpty()) {
            return response()->json(['message' => 'No se encontraron cuotas para este alumno.'], 404);
        }

        // Crear un array para las cuotas pagadas
        $cuotasPagadas = [];

        // Revisar el estado de cada cuota
        foreach ($cuotas as $cuota) {
            for ($i = 1; $i <= 10; $i++) {
                $cuotaKey = "cuota_$i";
                $estadoKey = "c{$i}_estado";

                if ($cuota->$estadoKey == 1) {
                    $cuotasPagadas[$cuotaKey] = $cuota->$cuotaKey;
                    $cuotasPagadas[$estadoKey] = $cuota->$estadoKey;
                }
            }
        }

        // Verificar si hay cuotas pagadas
        if (empty($cuotasPagadas)) {
            return response()->json(['message' => 'No se encontraron cuotas pagadas para este alumno.'], 404);
        }

        // Devolver las cuotas pagadas asociadas al alumno en formato JSON
        return response()->json([
            'dni_alumno' => $alumno->persona->dni,
            'cuotas' => $cuotasPagadas
        ]);
    }



    public function cuotaDetalles($id_grado)
    {
        // Obtener las cuotas del grado especificado
        $cuotas = Cuota::join('matricula', 'cuota.id_matricula', '=', 'matricula.id')
            ->join('alumno', 'matricula.id_alumno', '=', 'alumno.id')
            ->join('persona', 'alumno.id_persona', '=', 'persona.id')
            ->where('matricula.id_grado', $id_grado)
            ->select(
                'persona.dni as dni_alumno',
                'persona.nombre',
                'persona.ap_paterno',
                'persona.ap_materno',
                'cuota.costo_matricula',
                'cuota.matricula_estado',
                'cuota.cuota_1',
                'cuota.c1_estado',
                'cuota.cuota_2',
                'cuota.c2_estado',
                'cuota.cuota_3',
                'cuota.c3_estado',
                'cuota.cuota_4',
                'cuota.c4_estado',
                'cuota.cuota_5',
                'cuota.c5_estado',
                'cuota.cuota_6',
                'cuota.c6_estado',
                'cuota.cuota_7',
                'cuota.c7_estado',
                'cuota.cuota_8',
                'cuota.c8_estado',
                'cuota.cuota_9',
                'cuota.c9_estado',
                'cuota.cuota_10',
                'cuota.c10_estado'
            )
            ->get();


        if ($cuotas->isEmpty()) {
            return response()->json(['message' => 'No se encontraron cuotas para el grado especificado.'], 404);
        }


        $resultado = $cuotas->map(function ($cuota) {
            $cuotasDetalladas = [];

            for ($i = 1; $i <= 10; $i++) {
                $cuotaKey = "cuota_$i";
                $estadoKey = "c{$i}_estado";


                if ($cuota->$cuotaKey > 0 || $cuota->$estadoKey == 1) {
                    $cuotasDetalladas[] = [
                        'cuota' => $i,
                        'monto' => $cuota->$cuotaKey,
                        'estado' => $cuota->$estadoKey,
                    ];
                }
            }

            return [
                'dni_alumno' => $cuota->dni_alumno,
                'nombre' => $cuota->nombre,
                'ap_paterno' => $cuota->ap_paterno,
                'ap_materno' => $cuota->ap_materno,
                'cuotas' => $cuotasDetalladas,
                'costo_matricula' => $cuota->costo_matricula,
                'matricula_estado' => $cuota->matricula_estado,
            ];
        });


        return response()->json($resultado);
    }
















    public function pagarMatricula(Request $request)
    {
        // Autenticación del usuario
        $user = JWTAuth::parseToken()->authenticate();

        // Obtener la persona asociada al usuario autenticado
        $persona = Persona::where('id', $user->id_persona)->first();

        if (!$persona) {
            return response()->json(['message' => 'No se encontró una persona asociada al usuario.'], 404);
        }

        // Obtener el alumno asociado a la persona
        $alumno = Alumno::where('id_persona', $persona->id)->first();

        if (!$alumno) {
            return response()->json(['message' => 'No se encontró un alumno asociado a la persona.'], 404);
        }

        // Buscar la matrícula del alumno
        $matricula = Matricula::where('id_alumno', $alumno->id)->first();

        if (!$matricula) {
            return response()->json(['message' => 'No se encontró una matrícula para el alumno.'], 404);
        }

        // Obtener la cuota asociada a la matrícula
        $cuota = Cuota::where('id_matricula', $matricula->id)->first();

        if (!$cuota) {
            return response()->json(['message' => 'No se encontraron cuotas para la matrícula.'], 404);
        }

        // Verificar el estado de matrícula
        if ($cuota->matricula_estado == 1) {
            return response()->json(['message' => 'La matrícula ya está pagada.'], 400);
        }

        $emailPersona = $persona->email ?? null;

        if (!$emailPersona) {
            return response()->json(['message' => 'No se encontró un correo electrónico asociado a esta persona.'], 400);
        }

        \MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));

        $preference = new Preference();

        $item = new Item();
        $item->title = "Pago de matrícula";
        $item->quantity = 1;
        $item->unit_price = (float)$cuota->costo_matricula; // Precio de la matrícula
        $preference->items = [$item];

        $payer = new Payer();
        $payer->email = $emailPersona;

        $preference->payer = $payer;

        $preference->external_reference = $cuota->id; // Puedes usar el ID de la cuota o matrícula

        $preference->back_urls = [
            "success" => url("/api/payment-realizado-matricula/{$matricula->id}"),
            "failure" => url("/api/payment-failure"),
            "pending" => url("/api/payment-pending"),
        ];

        $preference->auto_return = "approved";

        $preference->save();

        return response()->json(['init_point' => $preference->init_point]);
    }

    public function pagoRealizadoMatricula(Request $request, $id)
    {
        \MercadoPago\SDK::setAccessToken(env('MERCADOPAGO_ACCESS_TOKEN'));

        // Obtener el ID del pago desde la consulta
        $payment_id = $request->query('payment_id');

        if (!$payment_id) {
            return response()->json(['message' => 'ID de pago no proporcionado.'], 400);
        }

        try {
            // Buscar el pago por su ID
            $payment = Payment::find_by_id($payment_id);

            if ($payment && $payment->status == 'approved') {
                // Obtener la cuota usando el ID proporcionado
                $cuota = Cuota::where('id_matricula', $id)->first();

                if ($cuota) {
                    // Verificar si el estado ya está pagado
                    if ($cuota->matricula_estado == 1) {
                        return response()->json(['message' => 'La matrícula ya está pagada.'], 400);
                    }

                    // Actualizar el estado de la matrícula
                    $cuota->matricula_estado = 1; // Marcar como pagada

                    // Vaciar el costo de la matrícula
                    $cuota->costo_matricula = 0.00; // Cambiar a 0

                    // Guardar los cambios en la base de datos
                    $cuota->save();

                    return response()->json(['message' => 'Pago de matrícula realizado con éxito.']);
                } else {
                    return response()->json(['message' => 'Cuota no encontrada.'], 404);
                }
            } else {
                return response()->json(['message' => 'El pago no fue aprobado o no se encontró.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al verificar el pago: ' . $e->getMessage()], 500);
        }
    }






    public function paymentSuccessMatricula($idMatricula)
    {
        // Verificar que el ID de matrícula sea válido
        if (!is_numeric($idMatricula)) {
            return response()->json(['message' => 'ID de matrícula no válido.'], 400);
        }

        // Obtener la cuota usando el ID recibido
        $cuota = Cuota::where('id_matricula', $idMatricula)->first();

        if (!$cuota) {
            return response()->json(['message' => 'Cuota no encontrada.'], 404);
        }

        // Verificar el estado de la matrícula
        if ($cuota->matricula_estado == 1) {
            return response()->json(['message' => 'La matrícula ya está pagada.'], 400);
        }

        // Actualizar el estado de la matrícula a pagada
        $cuota->matricula_estado = 1; // Marcar como pagada
        $cuota->save();

        return response()->json(['message' => 'Pago procesado exitosamente.']);
    }



    public function notificacionPagosMatricula(Request $request)
    {
        // Obtener el estado de la notificación
        $notificationData = $request->all();

        // Puedes validar el estado del pago aquí
        // Por ejemplo, verificar si el estado es "approved"
        $paymentId = $notificationData['data']['id'];
        $paymentStatus = $notificationData['data']['status'];

        // Realiza la lógica necesaria según el estado del pago
        if ($paymentStatus === 'approved') {
            // Buscar el ID de la matrícula a través del ID del pago o referencia
            $matriculaId = $notificationData['data']['external_reference'];
            $matricula = Matricula::find($matriculaId);

            if ($matricula) {
                // Actualizar el estado de la matrícula
                $matricula->matricula_estado = 1; // Marcar como pagada
                $matricula->save();
            }
        }

        // Retornar una respuesta adecuada
        return response()->json(['message' => 'Notificación recibida correctamente.']);
    }
}
