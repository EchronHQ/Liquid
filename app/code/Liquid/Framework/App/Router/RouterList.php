<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Router;

use Liquid\Framework\ObjectManager\ObjectManager;


class RouterList
{
    private array $routerList = [];

    public function __construct(
        private readonly ObjectManager $objectManager,
        array                          $routerList
    )
    {
        $this->routerList = \array_filter(
            $routerList,
            static function ($item) {
                return (!isset($item['disable']) || !$item['disable']) && $item['class'];
            }
        );
        \uasort($this->routerList, [$this, 'compareRoutersSortOrder']);
    }

    /**
     * Get list with available router ids
     *
     * @return string[]
     */
    public function getRouterIds(): array
    {
        return \array_keys($this->routerList);
    }

    /**
     * Retrieve router instance by id
     *
     * @param string $routerId
     * @return RouterInterface
     */
    public function getRouterInstance(string $routerId): RouterInterface
    {
        if (!isset($this->routerList[$routerId]['object'])) {
            $this->routerList[$routerId]['object'] = $this->objectManager->create(
                $this->routerList[$routerId]['class']
            );
        }
        return $this->routerList[$routerId]['object'];
    }

    /**
     * Compare routers sortOrder
     *
     * @param array $routerDataFirst
     * @param array $routerDataSecond
     * @return int
     */
    protected function compareRoutersSortOrder(array $routerDataFirst, array $routerDataSecond): int
    {
        return (int)$routerDataFirst['sortOrder'] <=> (int)$routerDataSecond['sortOrder'];
    }
}
