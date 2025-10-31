<?php
declare(strict_types=1);

namespace Liquid\Framework\Database\Sql;


use Liquid\Framework\App\DeploymentConfig;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class SqlFactory
{
    public function __construct(
        private readonly DeploymentConfig       $deploymentConfig,
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    public function create(): Sql
    {
        $arguments = [
            'database' => $this->deploymentConfig->getValue('database/database'),
            'username' => $this->deploymentConfig->getValue('database/username'),
            'password' => $this->deploymentConfig->getValue('database/password'),
            'host' => $this->deploymentConfig->getValue('database/host'),
            'port' => $this->deploymentConfig->getValue('database/port', 3306),
        ];
        return $this->objectManager->create(Sql::class, $arguments);
    }
}
