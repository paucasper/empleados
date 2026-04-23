<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SapVacationService
{
    public function getSummary(string $pernr): array
    {
        $baseUrl = rtrim(env('SAP_ADAPTER_URL'), '/');

        $response = Http::timeout(15)->get($baseUrl . '/api/vacations/summary/' . $pernr);

        if (! $response->successful()) {
            return [
                'pernr' => $pernr,
                'periodoInicio' => null,
                'periodoFin' => null,
                'totalVacaciones' => 0,
                'disponibles' => 0,
                'concedidos' => 0,
                'enTramite' => 0,
                'contingentes' => [],
            ];
        }

        return $response->json();
    }
}