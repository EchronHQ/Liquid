<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Action;

use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class ActionFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    /**
     * Create action
     *
     * @template T implements ActionInterface
     *
     * @param class-string<T> $actionName
     * @return T
     */
    public function create(string $actionName, array $arguments = []): ActionInterface
    {
        if (!is_subclass_of($actionName, ActionInterface::class)) {
            throw new \InvalidArgumentException(
                'The action name provided is invalid. Verify the action name and try again.'
            );
        }
        return $this->objectManager->create($actionName, $arguments);
    }
}
