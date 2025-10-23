<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/metrics', function () {
    static $startTime;
    if (!$startTime) {
        $startTime = microtime(true);
    }

    $uptime = microtime(true) - $startTime;
    $users = DB::table('users')->count();

    $metrics = <<<EOT
# HELP app_uptime_seconds Uptime of the Laravel app in seconds
# TYPE app_uptime_seconds gauge
app_uptime_seconds {$uptime}

# HELP app_users_total Total number of users
# TYPE app_users_total gauge
app_users_total {$users}

EOT;

    return response($metrics, 200)->header('Content-Type', 'text/plain');
});
