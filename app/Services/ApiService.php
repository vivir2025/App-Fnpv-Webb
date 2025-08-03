<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.external_api.url', 'http://fnpvi.nacerparavivir.org/api');
        $this->token = session('token');
    }

    public function get($endpoint, $params = [])
    {
        try {
            return Http::withToken($this->token)
                ->timeout(30)
                ->get($this->baseUrl . '/' . $endpoint, $params);
        } catch (\Exception $e) {
            Log::error("Error en GET {$endpoint}: " . $e->getMessage());
            throw $e;
        }
    }

    public function post($endpoint, $data = [])
    {
        try {
            return Http::withToken($this->token)
                ->timeout(30)
                ->post($this->baseUrl . '/' . $endpoint, $data);
        } catch (\Exception $e) {
            Log::error("Error en POST {$endpoint}: " . $e->getMessage());
            throw $e;
        }
    }

    public function put($endpoint, $data = [])
    {
        try {
            return Http::withToken($this->token)
                ->timeout(30)
                ->put($this->baseUrl . '/' . $endpoint, $data);
        } catch (\Exception $e) {
            Log::error("Error en PUT {$endpoint}: " . $e->getMessage());
            throw $e;
        }
    }

    public function delete($endpoint)
    {
        try {
            return Http::withToken($this->token)
                ->timeout(30)
                ->delete($this->baseUrl . '/' . $endpoint);
        } catch (\Exception $e) {
            Log::error("Error en DELETE {$endpoint}: " . $e->getMessage());
            throw $e;
        }
    }
    public function getEnviosPorFechaSalida($fecha)
    {
        return $this->get("envio-muestras/fecha-salida/{$fecha}");
    }
}
