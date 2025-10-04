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
            'name'              => "superadmin",
            'email'             => 'superadmin@demo.com',
            'password'          => Hash::make('demo'),
            'email_verified_at' => now(),
        ]);

        // $demoUser2 = User::create([
        //     'name'              => "Team Leader",
        //     'email'             => 'leader@demo.com',
        //     'password'          => Hash::make('demo'),
        //     'email_verified_at' => now(),
        // ]);

        // $demoUser3 = User::create([
        //     'name'              => "Marketing",
        //     'email'             => 'marketing@demo.com',
        //     'password'          => Hash::make('demo'),
        //     'email_verified_at' => now(),
        // ]);


        // $demoUser4 = User::create([
        //     'name'              => "FEN",
        //     'email'             => 'fen@demo.com',
        //     'password'          => Hash::make('q1w2e3r4'),
        //     'email_verified_at' => now(),
        // ]);

        // $demoUser5 = User::create([
        //     'name'              => "LUSI",
        //     'email'             => 'lusi@demo.com',
        //     'password'          => Hash::make('q1w2e3r4'),
        //     'email_verified_at' => now(),
        // ]);

        // $demoUser6 = User::create([
        //     'name'              => "FERNANDO",
        //     'email'             => 'fernando@demo.com',
        //     'password'          => Hash::make('q1w2e3r4'),
        //     'email_verified_at' => now(),
        // ]);

        // $demoUser7 = User::create([
        //     'name'              => "CINDY",
        //     'email'             => 'cindy@demo.com',
        //     'password'          => Hash::make('q1w2e3r4'),
        //     'email_verified_at' => now(),
        // ]);
    }
}
