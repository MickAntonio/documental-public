<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('templates')->insert([
            'nome' => 'Contracto de Trabalho PT',
            'activo' => 1,
            'descricao' => Faker::create()->words(9, true),
            'template' => "data:application/vnd.openxmlformats-officedocument.wordprocessingml.docume" ]);
    }
}
