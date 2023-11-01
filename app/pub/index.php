<?php
declare(strict_types=1);

use Liquid\Core\Application;

try {
    require __DIR__ . '/../app/bootstrap.php';

    $app = new Application();
    $app->run();
} catch (Throwable $ex) {
    // TODO: only show this in developer mode
    echo '<div>' . $ex->getMessage() . '</div>';
    echo '<pre>' . $ex->getTraceAsString() . '</pre>';

    http_response_code(500);
    exit(1);
}
