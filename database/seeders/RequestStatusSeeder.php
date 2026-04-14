<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['code' => 'draft', 'name' => 'Borrador'],
            ['code' => 'pending_approval', 'name' => 'Pendiente de aprobación'],
            ['code' => 'approved', 'name' => 'Aprobado'],
            ['code' => 'rejected', 'name' => 'Rechazado'],
            ['code' => 'sent_to_sap', 'name' => 'Enviado a SAP'],
            ['code' => 'sap_error', 'name' => 'Error en SAP'],
        ];

        foreach ($statuses as $status) {
            DB::table('request_statuses')->updateOrInsert(
                ['code' => $status['code']],
                [
                    'name' => $status['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}