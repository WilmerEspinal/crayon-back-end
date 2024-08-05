<?php

namespace App\Http\Controllers\ReniecConsultas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

use function Pest\Laravel\json;

class ReniecConsultasController extends Controller
{
    //
    public function consultasDNI($dni)
    {
        $token = 'apis-token-9708.8-JCVma1iCFpz5l0NFMYlmkVQN7HDRgi';

        $client = new Client(['base_uri' => 'https://api.apis.net.pe', 'verify' => false]);
        $parameters = [
            'http_errors' => false,
            'connect_timeout' => 5,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Referer' => 'https://apis.net.pe/api-consulta-dni',
                'User-Agent' => 'laravel/guzzle',
                'Accept' => 'application/json',
            ],
            'query' => ['numero' => $dni]
        ];
        $res = $client->request('GET', '/v2/reniec/dni', $parameters);
        $response = json_decode($res->getBody()->getContents(), true);
        return response()->json($response);
    }
}
