<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Cipi Admin',
            'email' => 'admin@cipi.sh',
            'password' => Hash::make('C1p1P4n3!#4.sh'),
        ]);

        Service::create([
            'name' => 'nginx',
            'slug' => 'nginx',
            'icon' => 'fas-n',
        ]);

        Service::create([
            'name' => 'PHP-FPM',
            'slug' => 'php',
            'icon' => 'fas-p',
        ]);

        Service::create([
            'name' => 'MySQL',
            'slug' => 'mysql',
            'icon' => 'fas-m',
        ]);

        Service::create([
            'name' => 'Redis',
            'slug' => 'redis',
            'icon' => 'fas-r',
        ]);

        Service::create([
            'name' => 'Supervisor',
            'slug' => 'supervisor',
            'icon' => 'fas-s',
        ]);
    }
}
