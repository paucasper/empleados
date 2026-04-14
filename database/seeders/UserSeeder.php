<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin1@dcoop.es'],
            [
                'name' => 'Antonio Pavón',
                'password' => Hash::make('admin1234'),
                'sap_employee_id' => '00000003',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('users')->updateOrInsert(
            ['email' => 'admin2@dcoop.es'],
            [
                'name' => 'Jose Luis Ortiz',
                'password' => Hash::make('admin1234'),
                'sap_employee_id' => '00000052',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}