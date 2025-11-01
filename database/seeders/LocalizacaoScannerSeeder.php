<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LocalizacaoScannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('localizacao_scanners')->insert(
            [
                'codigo' => '01',
                'nome' => 'Recepção'
            ],
            [
                'codigo' => '02',
                'nome' => 'Departamento de Recursos Humanos'
            ],
            [
                'codigo' => '03',
                'nome' => 'Sala de Reunião'
            ],
            [
                'codigo' => '04',
                'nome' => 'Departamento Financeiro'
            ],
        );
    }

}
