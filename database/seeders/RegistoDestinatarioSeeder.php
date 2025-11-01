<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\Registo;
use App\Models\Entidade;

class RegistoDestinatarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $registo  = Registo::orderBy('id', 'desc')->get()->first()->id;
        $entidade = Entidade::orderBy('id', 'desc')->get()->first()->id;
        DB::table('registo_destinatarios')->insert([
            'tipo' => 'ENTIDADE',
            'entidadeId' => $entidade,
            'registoId' => $registo,
            'visualizado' => false
        ]);
    }
}
