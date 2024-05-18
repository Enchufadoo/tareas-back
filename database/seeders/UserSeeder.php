<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory()->create([
            'name' => 'Ariel',
            'email' => 'ariel@capo.com',
            'username' => 'arielcapo123',
        ]);
        //\App\Models\User::factory(10)->create();
    }
}
