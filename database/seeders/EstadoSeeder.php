<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class EstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('estados')->insert(
            [
                'nome'=> 'NOVO',
                'type'=>'DOC'
            ],
            [
                'nome'=> 'EM ELABORAÇÃO',
                'type'=>'DOC'
            ],
            [
                'nome'=> 'CONCLUÍDO',
                'type'=>'DOC'
            ],
            [
                'nome'=> 'ARQUIVADO',
                'type'=>'DOC'
            ],
            [
                'nome'=> 'REGISTADO',
                'type'=>'DOC'
            ],
        );
    }
}
