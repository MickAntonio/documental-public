<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tipos')->insert([
            'nome' => 'Contracto de Trabalho',
            'descricao' => 'contracto de trabalho para nacionais',
            'activo' => 1,
            'type' => 'DOC',
            'confidencialidade' => 1,
            'atributosDinamico' => 1,
        ]);
    }
}
