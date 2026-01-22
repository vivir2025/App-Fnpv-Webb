<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearDashboardCache extends Command
{
    protected $signature = 'dashboard:clear-cache';
    protected $description = 'Limpiar caché del dashboard';

    public function handle()
    {
        $keys = [
            'sedes_dashboard',
            'sedes_map',
            'auxiliares_list'
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // También limpiar patrones de caché
        Cache::flush();

        $this->info('✓ Caché del dashboard limpiado correctamente');
        return 0;
    }
}
