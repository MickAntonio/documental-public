<?php

namespace Database\Seeders;

use App\Models\Tipo;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AtributoDinamicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tipo = Tipo::orderBy('id', 'desc')->get()->first()->id;

        DB::table('atributo_dinamicos')->insert([
            [
                'key' => 'tipoContrato',
                'value' => '',
                'label' => 'Tipo de Contrato',
                'required' => true,
                'order' => '1',
                'controlType' => 'select',
                'type' => 'number',
                'options' => 
                'Determinado
                 Indeterminado',
                'tipoId' => $tipo,
            ],
            [
                'key' => 'valor',
                'value' => '10000',
                'label' => 'Valor',
                'required' => true,
                'order' => '2',
                'controlType' => 'textbox',
                'type' => 'number',
                'options' => '100',
                'tipoId' => $tipo
            ]
        ]
        );
    }
}
