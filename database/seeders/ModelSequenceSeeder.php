<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Config\ModelSequence;
use Illuminate\Support\Facades\DB;

class ModelSequenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('model_sequences')->insert([
            'name' => 'Numero de documento',
            'code' => 'DOC',
            'year' => date("Y"),
            'prefix' => 'DOC-N',
        ]);

        DB::table('model_sequences')->insert([
            'name' => 'Numero da Etiqueta',
            'code' => 'ETIQUETA',
            'year' => date("Y"),
            'prefix' => 'I',
            'padding' => 4,
        ]);
    }
}
