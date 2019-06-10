<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	User::query()->truncate();

		return User::create([
			'name' => env('USER_NAME', 'Cipi Admin'),
			'email' => env('USER_EMAIL', 'admin@admin.com'),
			'password' => Hash::make(env('USER_PASSWORD', '12345678')),
            'email_verified_at' => Carbon::now(),
		 ]);
    }
}
