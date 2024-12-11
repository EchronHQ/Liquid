<?php
declare(strict_types=1);

namespace Liquid\Framework\Database\Sql;


use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class SqlFactory
{
    public function __construct(
        private readonly ScopeConfig            $config,
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    public function create(): Sql
    {
        $arguments = [
            'database' => $this->config->get('system', 'database.database'),
            'username' => $this->config->get('system', 'database.username'),
            'password' => $this->config->get('system', 'database.password'),
            'host' => $this->config->get('system', 'database.host'),
            'port' => $this->config->get('system', 'database.port', 3306),
        ];
        return $this->objectManager->create(Sql::class, $arguments);
    }
}
