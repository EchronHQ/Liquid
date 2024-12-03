<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class NoRouteHandlerList
{
    /**
     * No route handlers instances
     *
     * @var NoRouteHandlerInterface[]
     */
    private array|null $_handlers = null;


    public function __construct(
        private readonly array                  $handlerClassesList,
        private readonly ObjectManagerInterface $objectManager
    )
    {
    }

    /**
     * Get noRoute handlers
     *
     * @return NoRouteHandlerInterface[]
     */
    public function getHandlers(): array
    {
        if (!$this->_handlers) {
            //sorting handlers list
            $sortedHandlersList = [];
            foreach ($this->handlerClassesList as $handlerInfo) {
                if (isset($handlerInfo['class']) && isset($handlerInfo['sortOrder'])) {
                    $sortedHandlersList[$handlerInfo['class']] = $handlerInfo['sortOrder'];
                }
            }

            asort($sortedHandlersList);

            //creating handlers
            $this->_handlers = [];
            foreach (array_keys($sortedHandlersList) as $handlerInstance) {
                $this->_handlers[] = $this->objectManager->create($handlerInstance);
            }
        }

        return $this->_handlers;
    }
}
