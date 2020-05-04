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
			'name'              => config('app.cipi_user'),
			'email'             => config('app.cipi_email'),
			'password'          => Hash::make(config('app.cipi_password')),
            'email_verified_at' => Carbon::now(),
            'appkey'            => md5(uniqid()),
            'appsecret'         => sha1(microtime())
		]);
    }
}
