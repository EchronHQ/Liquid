<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

\define('ROOT', \dirname(__DIR__) . DIRECTORY_SEPARATOR);

require_once ROOT . 'vendor/autoload.php';

date_default_timezone_set('UTC');

/*  For data consistency between displaying (printing) and serialization a float number */
ini_set('precision', 14);
ini_set('serialize_precision', 14);
