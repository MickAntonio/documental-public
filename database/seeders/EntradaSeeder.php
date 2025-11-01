<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\Empresa;
use App\Models\Estado;
use App\Models\Tipo;
//use App\Models\Empresa;

class EntradaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $empresa = Empresa::orderBy('id', 'desc')->get()->first()->id;
        $estado = Estado::orderBy('id', 'desc')->get()->first()->id;
        $tipo = Tipo::orderBy('id', 'desc')->get()->first()->id;

        if (env('REALM_BASE_URL')== 'http://localhost:8080') {
            $utilizadorId = 'e9e91e6d-1bfe-4fc4-8da1-3eb6d162799b';
        }else{
            $utilizadorId = '628f4e62-0d6d-40d0-a860-78dac391b730';
        }

        DB::table('registos')->insert([
            'referencia' => 'REF0002',
            'assunto' => Faker::create()->words(2, true),
            'criadoPor' => 'Kaila S.S',
            'localizacaoFisica' => Faker::create()->words(3, true),
            'data' => '2023-02-02',
            'confidencial' => 1,
            'tipoId' => $tipo,
            'estadoId' => $estado,
            'utilizadorId' => $utilizadorId,
            'registoTipo' => "ENTRADA",
        ]);
    }
}
