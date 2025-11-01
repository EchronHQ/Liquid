<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use Liquid\Framework\App\Cache\Type\FileInfo;
use Liquid\Framework\Serialize\Serializer\Serialize;
use Psr\Log\LoggerInterface;

/**
 * @deprecated Replace this with file driver implementation
 */
readonly class FileHelper
{
    private const KEY_PREFIX_FILE_EXIST = 'file-exist';
    private bool $enableCache;

    public function __construct(
        private FileInfo        $cache,
        private Serialize       $serialize,
        private LoggerInterface $logger
    )
    {
        $this->enableCache = false;// $this->configuration->getValueBoolean('cache.types.file');

    }

    public function setFileExist(string $path, bool $exist): void
    {
        $cacheKey = $this->cleanupPathForKey($path, self::KEY_PREFIX_FILE_EXIST);
        $saved = $this->cache->save($exist ? '1' : '0', $cacheKey, [], new \DateInterval('P5D'));
    }

    public function clearFileExists(string $path): void
    {
        $cacheKey = $this->cleanupPathForKey($path, self::KEY_PREFIX_FILE_EXIST);
        $unset = $this->cache->remove($cacheKey);
    }

    /**
     * @param string $path
     * @return array{width:int,height:int}|null
     */
    public function getImageDimensions(string $path, bool $allowCache = true): array|null
    {
        $cacheKey = 'filedimensions-' . $path;
        if ($allowCache && $this->enableCache && $this->cache->test($cacheKey) !== null) {
            $value = $this->cache->load($cacheKey);
            if (\is_string($value)) {
                return $this->serialize->unserialize($value);
            }
        }


        $size = \getimagesize($path);
        if ($size === false) {
            return null;
        }
        [$width, $height, $type, $attr] = $size;
        $value = ['width' => $width, 'height' => $height];
        $this->logger->debug('[FileHelper] Get file dimensions ' . $path);
        if ($allowCache && $this->enableCache) {
            $saved = $this->cache->save($this->serialize->serialize($value), $cacheKey, [], new \DateInterval('P5D'));
        }
        return $value;
    }

    public function getFileContent(string $path, bool $allowCache = true): string
    {
        $cacheKey = 'filecontent-' . $path;
        if ($allowCache && $this->cache->test($cacheKey) !== null) {
            $value = $this->cache->load($cacheKey);
            if (\is_string($value)) {
                return $value;
            }
        }
        $value = \file_get_contents($path);

        $this->logger->debug('[FileHelper] Get file content ' . $path);
        if ($allowCache) {
            $saved = $this->cache->save($value, $cacheKey, [], new \DateInterval('P5D'));
        }
        return $value;
    }

    public function getFileModificationTime(string $path, bool $allowCache = true): \DateTime|null
    {
        $cacheKey = 'filemodification-' . $path;
        if ($allowCache && $this->cache->test($cacheKey) !== null) {
            $cachedResult = $this->cache->load($cacheKey);

            if (\is_string($cachedResult)) {
                return \DateTime::createFromFormat('U', $cachedResult);
            }
        }
        if ($this->fileExist($path)) {
            $rawResult = \filemtime($path);
            if ($rawResult === false) {
                return null;
            }

            if ($allowCache) {
                $saved = $this->cache->save((string)$rawResult, $cacheKey, [], new \DateInterval('P5D'));
            }

            return \DateTime::createFromFormat('U', $rawResult . '');
        }
        return null;
    }

    public function fileExist(string $path, bool $allowCache = true): bool
    {
        $path = \str_replace('//', '/', $path);
        $cacheKey = $this->cleanupPathForKey($path, self::KEY_PREFIX_FILE_EXIST);
        if ($allowCache && $this->enableCache) {

            $x = $this->cache->load($cacheKey);
            if ($x === '1') {
                return true;
            }

            if ($x === '0') {
                return false;
            }
//
//            if (is_bool($x)) {
//                return $x;
//            }
        }

        $value = !empty($path) && \file_exists($path) && \is_file($path);
        // $this->logger->warning('[FileHelper] Get file exist ' . $path . ' (result: ' . ($value ? 'Yes' : 'No') . ')');
        // Store for 5 days
        if ($allowCache && $this->enableCache) {
            $saved = $this->cache->save($value ? '1' : '0', $cacheKey, [], new \DateInterval('P5D'));
        }
        return $value;
    }

    public function copyFile(string $source, string $destination, bool $overWriteIfExists = false): void
    {

        if (!$this->fileExist($source)) {
            throw new \Error('Source file does not exist');
        }
        if (!$overWriteIfExists && $this->fileExist($destination)) {
            throw new \Exception('Unable to copy file, destination already exist');
        }
        $copied = \copy($source, $destination);
        if (!$copied) {
            throw new \Error('File not copied');
        }
    }

    private function cleanupPathForKey(string $path, string $prefix = ''): string
    {
        return $prefix . '-' . \str_replace(['.'], [''], \strtolower($path));
    }


}
