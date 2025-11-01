<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\Registo;
use App\Models\Documento;
class DocumentoAssociadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $registo = Registo::orderBy('id','desc')->get()->first()->id;
        $documento = Documento::orderBy('id','desc')->get()->first()->id;
        DB::table('documento_associados')->insert([
        'utilizador'=>'1',
        'documentoId'=>$documento,
        'registoId'=>$registo,
        ]);
    }
}
