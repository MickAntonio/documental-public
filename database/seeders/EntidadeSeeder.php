<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\EntidadeTipo;
use Faker\Factory as Faker;

class EntidadeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $entidadeTipo = EntidadeTipo::orderBy('id', 'desc')->get()->first()->id;
        
        DB::table('entidades')->insert([
            'nome' => 'Kaila Formação e Transformação',
            'nif' => Faker::create()->numerify('#########'),
            'codigo' => Faker::create()->numerify('#########'),
            'telefone' => Faker::create()->numerify('#########'),
            'activo' => 1,
            'endereco' => Faker::create()->words(5, true),
            'email' => 'geral.formacao@kaila.co.ao',
            'entidadeTipoId' => $entidadeTipo,
        ]);
    }
}
