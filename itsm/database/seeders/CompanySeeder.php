<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'id' => 1,
                'code' => 'ASM', // <-- Tambahkan kode perusahaan
                'name' => 'PT Amarin Ship Management',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'code' => 'CTP', // <-- Tambahkan kode perusahaan
                'name' => 'PT Caraka Tirta Pratama',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'code' => 'ACS', // <-- Tambahkan kode perusahaan
                'name' => 'Amarin Crewing Services',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('companies')->upsert($companies, ['id'], ['code', 'name', 'is_active', 'updated_at']);
    }
}