<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use Psr\Log\LoggerInterface;

readonly class FileHelper
{
    public function __construct(private CacheHelper $cache, private LoggerInterface $logger)
    {

    }

    public function fileExist(string $path, bool $allowCache = true): bool
    {
        $cacheKey = 'fileexist-' . $path;
        if ($allowCache && $this->cache->has($cacheKey)) {
            $x = $this->cache->get($cacheKey);

            if (is_bool($x)) {
                return $x;
            }
        }
        //$this->logger->debug('[FileHelper] Get file exist ' . $path);
        $value = !empty($path) && \file_exists($path) && \is_file($path);

        // Store for 5 days
        if ($allowCache) {
            $saved = $this->cache->set($cacheKey, $value, new \DateInterval('P5D'));
        }
        return $value;
    }

    public function setFileExist(string $path, bool $exist): void
    {
        $cacheKey = 'fileexist-' . $path;
        $saved = $this->cache->set($cacheKey, $exist, new \DateInterval('P5D'));
    }

    /**
     * @param string $path
     * @return array{width:int,height:int}|null
     */
    public function getImageDimensions(string $path): array|null
    {
        // TODO: cache this
        $size = getimagesize($path);
        if ($size === false) {
            return null;
        }
        list($width, $height, $type, $attr) = $size;
        return ['width' => $width, 'height' => $height];
    }

    public function getFileContent(string $path, bool $cache = true): string
    {
        $cacheKey = 'filecontent-' . $path;
        if ($cache && $this->cache->has($cacheKey)) {
            $value = $this->cache->get($cacheKey);
            if (is_string($value)) {
                return $value;
            }
        }
        $value = \file_get_contents($path);

        $this->logger->debug('[FileHelper] Get file content ' . $path);
        if ($cache) {
            $saved = $this->cache->set($cacheKey, $value, new \DateInterval('P5D'));
        }
        return $value;
    }

    public function getFileModificationTime(string $path, bool $allowCache = true): \DateTime|null
    {
        $cacheKey = 'filemodification-' . $path;
        if ($allowCache && $this->cache->has($cacheKey)) {
            $cachedResult = $this->cache->get($cacheKey);

            if (is_numeric($cachedResult)) {
                return \DateTime::createFromFormat('U', $cachedResult . '');
            }
        }
        if ($this->fileExist($path)) {
            $rawResult = filemtime($path);
            if ($rawResult === false) {
                return null;
            }

            if ($allowCache) {
                $saved = $this->cache->set($cacheKey, $rawResult, new \DateInterval('P5D'));
            }

            return \DateTime::createFromFormat('U', $rawResult . '');
        }
        return null;
    }


}
