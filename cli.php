<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo 'cli must be run as a CLI application';
    exit(1);
}
//apc_cache_clear();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

use Liquid\Core\Cli;

try {
    require __DIR__ . '/app/bootstrap.php';

    $app = new Cli(\ROOT);
    $app->run();
} catch (Throwable $ex) {
    // TODO: only show this in developer mode
    echo $ex->getMessage();
    echo $ex->getTraceAsString();
}
