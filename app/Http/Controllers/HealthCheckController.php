<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $status = 'healthy';
        $checks = [];

        // Verificar base de datos
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'healthy',
                'message' => 'Conexión establecida'
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'unhealthy',
                'message' => $e->getMessage()
            ];
            $status = 'unhealthy';
        }

        // Verificar Redis (si está configurado)
        try {
            Redis::ping();
            $checks['redis'] = [
                'status' => 'healthy',
                'message' => 'Conexión establecida'
            ];
        } catch (\Exception $e) {
            $checks['redis'] = [
                'status' => 'unhealthy', 
                'message' => 'Redis no disponible'
            ];
        }

        // Verificar almacenamiento
        try {
            Storage::disk('local')->put('healthcheck.txt', 'test');
            Storage::disk('local')->delete('healthcheck.txt');
            $checks['storage'] = [
                'status' => 'healthy',
                'message' => 'Almacenamiento funcionando'
            ];
        } catch (\Exception $e) {
            $checks['storage'] = [
                'status' => 'unhealthy',
                'message' => $e->getMessage()
            ];
            $status = 'unhealthy';
        }

        // Información del sistema
        $checks['system'] = [
            'status' => 'healthy',
            'message' => 'Sistema operativo',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'max_memory' => ini_get('memory_limit'),
        ];

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
            'checks' => $checks,
        ], $status === 'healthy' ? 200 : 503);
    }
}