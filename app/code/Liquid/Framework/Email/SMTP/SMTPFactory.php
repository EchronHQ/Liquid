<?php
declare(strict_types=1);

namespace Liquid\Framework\Email\SMTP;

use Liquid\Framework\App\DeploymentConfig;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class SMTPFactory
{
    public function __construct(
        private readonly DeploymentConfig       $deploymentConfig,
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    public function create(): SMTP
    {
        $arguments = [
            'host' => $this->deploymentConfig->getValue('smtp/default/host'),
            'port' => $this->deploymentConfig->getValue('smtp/default/port'),
            'tls' => $this->deploymentConfig->getValueBoolean('smtp/default/tls'),
            'username' => $this->deploymentConfig->getValue('smtp/default/username'),
            'password' => $this->deploymentConfig->getValue('smtp/default/password'),
        ];
        return $this->objectManager->create(SMTP::class, $arguments);
    }
}
