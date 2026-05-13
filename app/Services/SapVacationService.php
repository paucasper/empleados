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

    public function checkDays(string $pernr, string $begda, string $endda, string $awart): int
    {
        $baseUrl = rtrim(env('SAP_ADAPTER_URL'), '/');

        $response = Http::timeout(15)->post($baseUrl . '/api/vacations/check', [
            'Pernr' => $pernr,
            'Begda' => $begda,
            'Endda' => $endda,
            'Awart' => $awart,
        ]);

        if (! $response->successful()) {
            return 0;
        }

        return (int) ($response->json('Dias') ?? $response->json('dias') ?? 0);
    }

    public function getAvailableDaysForAwart(string $pernr, string $awart): float
    {
        $summary = $this->getSummary($pernr);

        $contingentes = $summary['Contingentes'] 
            ?? $summary['contingentes'] 
            ?? [];

        foreach ($contingentes as $contingente) {
            $codigo = $contingente['CodigoAusencia'] 
                ?? $contingente['codigoAusencia'] 
                ?? null;

            if ((string) $codigo === (string) $awart) {
                $total = (float) ($contingente['Total'] ?? $contingente['total'] ?? 0);
                $consumidos = (float) ($contingente['Consumidos'] ?? $contingente['consumidos'] ?? 0);

                return max(0, $total - $consumidos);
            }
        }

        return 0;
    }
}