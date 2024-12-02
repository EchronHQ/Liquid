<?php
declare(strict_types=1);

namespace Liquid\Framework\App;

use Liquid\Framework\App\Response\ResponseInterface;

interface AppInterface
{
    public function launch(): ResponseInterface;
}
