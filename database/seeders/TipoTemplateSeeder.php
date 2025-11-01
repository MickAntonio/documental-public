<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Template;
use App\Models\Tipo;

class TipoTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $template= Template::orderBy('id','desc')->get()->first()->id;
        $tipo = Tipo::orderBy('id','desc')->get()->first()->id;
          DB::table('tipo_templates')->insert([
            'tipoId'=>$tipo,
            'templateId'=>$template,
        ]);
    }
}
