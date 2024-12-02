<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Action;

use Liquid\Framework\Controller\ResultInterface;

interface ActionInterface
{
    public function execute(): ResultInterface;
}
