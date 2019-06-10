<?php

namespace App\Helpers;

use Ping;

class Helper
{

    public static function sslcheck($domain) {
    	if(Ping::check('http://'.$domain) == 200) {
	        $ssl_check = @fsockopen( 'ssl://' . $domain, 443, $errno, $errstr, 30 );
		    $res = !! $ssl_check;
		    if($ssl_check) { fclose( $ssl_check ); }
		    return $res;
		} else {
			return 0;
		}

    }

}