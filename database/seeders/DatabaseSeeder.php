<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => config('panel.admin.name'),
            'email' => config('panel.admin.email'),
            'password' => Hash::make(
                config('panel.admin.password')
            ),
        ]);
    }
}
