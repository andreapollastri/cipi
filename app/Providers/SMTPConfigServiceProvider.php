<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class SMTPConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(\Schema::hasTable('smtps')) {
            $mail = DB::table('smtps')->first();
            if($mail && $mail->host != 'smtp.yourdomain.ltd') {
                $config = array(
                    'driver'     => 'smtp',
                    'host'       => $mail->host,
                    'port'       => $mail->port,
                    'from'       => array('address' => $mail->from, 'name' => 'Cipi Control Panel'),
                    'encryption' => $mail->encryption,
                    'username'   => $mail->username,
                    'password'   => $mail->password,
                    'sendmail'   => '/usr/sbin/sendmail -bs',
                    'pretend'    => false,
                );
                \Config::set('mail', $config);
            }
        }
    }
}
