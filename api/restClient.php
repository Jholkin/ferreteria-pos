<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use GuzzleHttp\Client;

$token = $_ENV['APIS_PERU_TOKEN'];

$client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'http://api.apis.net.pe/v2/reniec/',
    // You can set any number of default request options.
    'timeout'  => 2.0,
]);

function obtenerDni($dni) {
    global $client, $token;
    try {
        $response = $client->request('GET', "dni", [
            'query' => ['numero' => $dni],
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        return [
            'success' => true,
            'data' => $data
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => 'Error al consultar el DNI: ' . $e->getMessage()
        ];
    }
}