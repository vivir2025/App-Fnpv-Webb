<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\ApiService;

class ApiController extends Controller
{
    protected $apiService;

    public function __construct(ApiService $apiService = null)
    {
        $this->apiService = $apiService ?? new ApiService();
    }

    public function getSedes(Request $request)
    {
        $token = session('token');
        
        try {
            $response = Http::withToken($token)
                ->get('http://fnpvi.nacerparavivir.org/api/sedes');
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([], 500);
        } catch (\Exception $e) {
            Log::error('Error al obtener sedes: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}