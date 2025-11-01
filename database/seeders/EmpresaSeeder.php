<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('empresas')->insert([
            'nome' => 'Kaila Systems Solutions',
            'nif' => '0123456789',
            'naturezaJuridica' => 'Empresa',
            'telefone' => '935378674',
            'actividadesEconomica' => 'Teste',
            'endereco' => 'Viana',
            'email' => 'kaila@gmail.com',
            'dataAbertura' => '2023-02-02',
        ]);
    }
}
