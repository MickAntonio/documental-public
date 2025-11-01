<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (env('REALM_BASE_URL')== 'http://localhost:8080') {
            $utilizadorId = 'e9e91e6d-1bfe-4fc4-8da1-3eb6d162799b';
        } else {
            $utilizadorId = '628f4e62-0d6d-40d0-a860-78dac391b730';
        }

        DB::table('users')->insert([
            'id' => $utilizadorId,
            'name' => 'user',
            'email' => 'nzunzi.antonio@outlook.com',
            'phone_number' => '992556018'
        ]);
    }
}
