<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Generator $faker)
    {
        $demoUser = User::create([
            'name'              => "Super Admin",
            'email'             => 'superadmin@demo.com',
            'password'          => Hash::make('demo'),
            'email_verified_at' => now(),
        ]);

        $demoUser2 = User::create([
            'name'              => "Team Leader",
            'email'             => 'leader@demo.com',
            'password'          => Hash::make('demo'),
            'email_verified_at' => now(),
        ]);

         $demoUser3 = User::create([
            'name'              => "Marketing",
            'email'             => 'marketing@demo.com',
            'password'          => Hash::make('demo'),
            'email_verified_at' => now(),
        ]);
    }
}
