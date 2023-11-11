<?php
declare(strict_types=1);

namespace Liquid\Content\Helper;

use Liquid\Framework\Filesystem\DirectoryList;
use Liquid\Framework\Filesystem\Path;

class StaticContentHelper
{
    private string|null $staticContentDeployedVersion = null;
    private string $versionFilePath;

    public function __construct(private readonly DirectoryList $directoryList)
    {
        $this->versionFilePath = $this->directoryList->getPath(Path::STATIC_VIEW) . DIRECTORY_SEPARATOR . 'deployed_version.txt';
    }

    public function getStaticDeployedVersion(): string
    {
        if ($this->staticContentDeployedVersion === null) {

            if (!file_exists($this->versionFilePath)) {
                throw new \Exception('Version file does not exists');
            }
            $version = \Safe\file_get_contents($this->versionFilePath);
            $version = trim($version);
            if ($version !== '') {
                $this->staticContentDeployedVersion = 'v' . $version;
            }
        }
        return $this->staticContentDeployedVersion;
    }

    public function updateStaticDeployedVersion(): void
    {
        file_put_contents($this->versionFilePath, gmdate('U'));
    }
}
