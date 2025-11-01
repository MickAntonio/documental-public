<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\DB;
use Database\Seeders\EmpresaSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
       
        $this->call([
            ModelSequenceSeeder::class,
            UserSeeder::class,
            EmpresaSeeder::class,
            EstadoSeeder::class,
            EntidadeTipoSeeder::class,
            EntidadeSeeder::class,
            TemplateSeeder::class,
            TipoSeeder::class,
            TipoTemplateSeeder::class,
            AtributoDinamicoSeeder::class,
            EntradaSeeder::class,
            DocumentoSeeder::class,
            DocumentoAssociadoSeeder::class,
            RegistoDestinatarioSeeder::class,
            RegistoRemetenteSeeder::class
        ]);
    }
}
