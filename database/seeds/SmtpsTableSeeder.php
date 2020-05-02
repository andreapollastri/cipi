<?php

use Illuminate\Database\Seeder;
use App\Smtp;

class SmtpsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Smtp::query()->truncate();

		return Smtp::create([

		]);
    }
}
