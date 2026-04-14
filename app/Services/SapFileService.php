<?php

namespace App\Services;
use App\Models\HrRequest;

class SapFileService
{
    public function generateAbsenceFile(array $data): string
    {
        $awart = $data['awart'];
        $pernr = $data['pernr'];
        $begda = $this->formatDate($data['begda']);
        $endda = $this->formatDate($data['endda']);

        return "{$awart};{$pernr};{$begda};{$endda}";
    }

    public function generateFileName(string $pernr): string
    {
        return 'AUS' . $pernr . now()->format('YmdHis') . '.txt';
    }

    public function saveFile(string $content, string $fileName): string
    {
        $path = storage_path('app/sap/ausencias');

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . $fileName;

        file_put_contents($fullPath, $content);

        return $fullPath;
    }

    private function formatDate(string $date): string
    {
        return str_replace('-', '', $date);
    }

    public function generateExpenseFile(HrRequest $expenseRequest): string
    {
        $lines = [];

        foreach ($expenseRequest->items as $item) {
            $lines[] = implode(';', [
                $expenseRequest->id,                                          // pernr_aux (ID solicitud)
                'GAST',                                                       // id_gasto (fijo)
                ltrim($expenseRequest->sap_employee_id, '0'),                 // tipo_fich (pernr sin ceros)
                $expenseRequest->id,                                          // num_solic
                (int) $item->expense_date->format('n'),                       // mes_gasto
                $item->expense_date->format('Y'),                             // anio_gasto
                $expenseRequest->description ?? '',                           // descrip_gasto
                $this->mapExpenseType($item->expense_type),                   // tipo_gasto
                number_format($item->unit_amount ?? 0, 2, ',', '.'),          // importe_gasto
                number_format($item->amount ?? 0, 2, ',', '.'),               // cantidad_gasto
                $item->expense_date->format('Ymd'),                           // fecha_gasto
                $item->is_card_payment ? 'SI' : 'NO',                        // ind_pagado_tarj
                $item->description ?? '',                                     // coment_gasto
            ]);
        }

        return implode("\n", $lines);
    }

    public function saveExpenseFile(string $content, string $fileName): string
    {
        $path = storage_path('app/sap/gastos');

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($fullPath, $content);

        return $fullPath;
    }

    public function generateExpenseFileName(string $pernr): string
    {
        return 'GAS' . $pernr . now()->format('YjnGis') . '.txt';
    }

    private function mapExpenseType(string $type): string
    {
        switch ($type) {
            case 'kilometraje':    return 'K';
            case 'otros_gastos':   return 'O';
            case 'media_dieta':    return 'MD';
            case 'dieta_completa': return 'DC';
            default:               return $type;
        }
    }
}