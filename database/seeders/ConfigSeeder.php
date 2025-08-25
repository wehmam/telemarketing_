<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Config::updateOrCreate(
            ['key' => 'allowed_ips'],
            ['value' => ['127.0.0.1', '192.168.1.100']]
        );
    }
}
