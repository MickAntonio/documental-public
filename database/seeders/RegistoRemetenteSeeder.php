<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\Registo;
use App\Models\Entidade;

class RegistoRemetenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $registo  = Registo::orderBy('id', 'desc')->get()->first()->id;

        if (env('REALM_BASE_URL')== 'http://localhost:8080') {
            $utilizadorId = 'e9e91e6d-1bfe-4fc4-8da1-3eb6d162799b';
        } else {
            $utilizadorId = '628f4e62-0d6d-40d0-a860-78dac391b730';
        }
        DB::table('registo_remetentes')->insert([
            'tipo' => 'ENTIDADE',
            'utilizadorId' => $utilizadorId,
            'registoId' => $registo,
        ]);
    }
}
