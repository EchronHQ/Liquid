<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use Liquid\Core\Model\AppConfig;
use Psr\Log\LoggerInterface;

readonly class FileHelper
{
    private bool $enableCache;
    private const KEY_PREFIX_FILE_EXIST = 'file-exist';

    public function __construct(
        private CacheHelper     $cache,
        private AppConfig       $configuration,
        private LoggerInterface $logger
    )
    {
        $this->enableCache = $this->configuration->getValueBoolean('cache.types.file');

    }

    public function fileExist(string $path, bool $allowCache = true): bool
    {
        $cacheKey = $this->cleanupPathForKey($path, self::KEY_PREFIX_FILE_EXIST);
        if ($allowCache && $this->enableCache && $this->cache->has($cacheKey)) {
            $x = $this->cache->get($cacheKey);

            if (is_bool($x)) {
                return $x;
            }
        }
        //$this->logger->debug('[FileHelper] Get file exist ' . $path);
        $value = !empty($path) && \file_exists($path) && \is_file($path);

        // Store for 5 days
        if ($allowCache && $this->enableCache) {
            $saved = $this->cache->set($cacheKey, $value, new \DateInterval('P5D'));
        }
        return $value;
    }

    public function setFileExist(string $path, bool $exist): void
    {
        $cacheKey = $this->cleanupPathForKey($path, self::KEY_PREFIX_FILE_EXIST);
        $saved = $this->cache->set($cacheKey, $exist, new \DateInterval('P5D'));
    }

    public function clearFileExists(string $path): void
    {
        $cacheKey = $this->cleanupPathForKey($path, self::KEY_PREFIX_FILE_EXIST);
        $unset = $this->cache->unset($cacheKey);
    }


    private function cleanupPathForKey(string $path, string $prefix = ''): string
    {
        return $prefix . '-' . str_replace(['.'], [''], strtolower($path));
    }

    /**
     * @param string $path
     * @return array{width:int,height:int}|null
     */
    public function getImageDimensions(string $path, bool $allowCache = true): array|null
    {
        $cacheKey = 'filedimensions-' . $path;
        if ($allowCache && $this->enableCache && $this->cache->has($cacheKey)) {
            $value = $this->cache->get($cacheKey);
            if (is_array($value)) {
                return $value;
            }
        }


        $size = getimagesize($path);
        if ($size === false) {
            return null;
        }
        [$width, $height, $type, $attr] = $size;
        $value = ['width' => $width, 'height' => $height];
        $this->logger->debug('[FileHelper] Get file dimensions ' . $path);
        if ($allowCache && $this->enableCache) {
            $saved = $this->cache->set($cacheKey, $value, new \DateInterval('P5D'));
        }
        return $value;
    }

    public function getFileContent(string $path, bool $allowCache = true): string
    {
        $cacheKey = 'filecontent-' . $path;
        if ($allowCache && $this->cache->has($cacheKey)) {
            $value = $this->cache->get($cacheKey);
            if (is_string($value)) {
                return $value;
            }
        }
        $value = \file_get_contents($path);

        $this->logger->debug('[FileHelper] Get file content ' . $path);
        if ($allowCache) {
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

    public function copyFile(string $source, string $destination, bool $overWriteIfExists = false): void
    {

        if (!$this->fileExist($source)) {
            throw new \Error('Source file does not exist');
        }
        if (!$overWriteIfExists && $this->fileExist($destination)) {
            throw new \Exception('Unable to copy file, destination already exist');
        }
        $copied = copy($source, $destination);
        if (!$copied) {
            throw new \Error('File not copied');
        }
    }


}
