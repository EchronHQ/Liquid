<?php
declare(strict_types=1);

namespace Liquid\Framework\Database\Sql;


use Liquid\Framework\App\Config\AppConfig;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class SqlFactory
{
    public function __construct(
        private readonly AppConfig              $config,
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    public function create(): Sql
    {
        $arguments = [
            'database' => $this->config->get('database.database'),
            'username' => $this->config->get('database.username'),
            'password' => $this->config->get('database.password'),
            'host' => $this->config->get('database.host'),
            'port' => $this->config->get('database.port', 3306),
        ];
        return $this->objectManager->create(Sql::class, $arguments);
    }
}
