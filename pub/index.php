<?php
declare(strict_types=1);

use Liquid\Core\Application;

try {
    require __DIR__ . '/../app/bootstrap.php';

    $app = new Application();
    $app->run();
} catch (Throwable $ex) {
    http_response_code(500);
    exit(1);
}
