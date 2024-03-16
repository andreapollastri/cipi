<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('panel:check-server-status', function () {
    // Check server status
})->everyMinute();
