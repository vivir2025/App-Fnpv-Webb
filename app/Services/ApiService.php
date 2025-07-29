<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        return Http::withToken($this->token)
            ->get($this->baseUrl . '/' . $endpoint, $params);
    }

    public function post($endpoint, $data = [])
    {
        return Http::withToken($this->token)
            ->post($this->baseUrl . '/' . $endpoint, $data);
    }

    public function put($endpoint, $data = [])
    {
        return Http::withToken($this->token)
            ->put($this->baseUrl . '/' . $endpoint, $data);
    }

    public function delete($endpoint)
    {
        return Http::withToken($this->token)
            ->delete($this->baseUrl . '/' . $endpoint);
    }
}
