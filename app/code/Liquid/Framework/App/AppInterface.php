<?php
declare(strict_types=1);

namespace Liquid\Framework\App;

use Liquid\Core\Application;
use Liquid\Framework\App\Response\ResponseInterface;

interface AppInterface
{
    /**
     * Launch application and returns a response
     *
     * @return ResponseInterface
     */
    public function launch(): ResponseInterface;

    /**
     * Ability to handle exceptions that may have occurred during bootstrap and launch
     *
     * Return values:
     * - true: exception has been handled, no additional action is needed
     * - false: exception has not been handled - pass the control to Bootstrap
     *
     * @param Application $bootstrap
     * @param \Throwable $exception
     * @return bool
     */
    public function catchException(Application $bootstrap, \Throwable $exception);
}
