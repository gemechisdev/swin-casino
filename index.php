<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ensure the working directory is always the web root so that relative asset
// paths (e.g. "assets/images/…") resolve correctly under PHP-FPM environments
// where the CWD is not guaranteed to be the document root.
chdir(__DIR__);

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/core/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/core/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/core/bootstrap/app.php')
    ->handleRequest(Request::capture());
