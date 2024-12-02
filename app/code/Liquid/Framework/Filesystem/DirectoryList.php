<?php
declare(strict_types=1);

namespace Liquid\Framework\Filesystem;

class DirectoryList
{
    public const PATH = 'path';
    public const URL_PATH = 'uri';

    private array $directories;
    private string $root;

    public function __construct(string $root)
    {
        $this->root = $this->normalizePath($root);
        $this->directories = static::getDefaultConfig();

        // Set system temp directory
        $sysTmpPath = get_cfg_var('upload_tmp_dir') ?: sys_get_temp_dir();
        $this->directories[Path::SYS_TMP->name] = [self::PATH => realpath($sysTmpPath)];

        //Set root directory
        $this->directories[Path::ROOT->name] = [self::PATH => $root];

        //Make paths absolute
        foreach ($this->directories as $code => $dir) {
            $path = $this->normalizePath($dir[self::PATH]);
            if (!$this->isAbsolute($path)) {
                $path = $this->prependRoot($path);
            }
            $this->directories[$code][self::PATH] = $path;

            if (isset($dir[self::URL_PATH])) {
                $this->assertUrlPath($dir[self::URL_PATH]);
            }
        }
    }

    private function normalizePath(string|null $path): string
    {
        return $path !== null ? str_replace('\\', '/', $path) : '';
    }

    private static function getDefaultConfig(): array
    {
        return [
            Path::SYS_TMP->name => [self::PATH => ''],

            Path::ROOT->name => [self::PATH => ''],
            Path::APP->name => [self::PATH => ''],
            Path::CONFIG->name => [self::PATH => 'app/etc'],
            Path::PUB->name => [self::PATH => 'pub', self::URL_PATH => ''],

            Path::MEDIA->name => [self::PATH => 'pub/media', self::URL_PATH => 'media'],

            Path::STATIC_VIEW->name => [self::PATH => 'pub/static', self::URL_PATH => 'static'],
        ];
    }

    private function isAbsolute(string|null $path): bool
    {
        $path = $path !== null ? str_replace('\\', '/', $path) . '' : '';

        if (str_starts_with($path, '/')) {
            //is UnixRoot
            return true;
        }

        if (preg_match('#^\w{1}:/#', $path)) {
            //is WindowsRoot
            return true;
        }

        if (parse_url($path, PHP_URL_SCHEME) !== null) {
            //is WindowsLetter
            return true;
        }

        return false;
    }

    private function prependRoot(string $path): string
    {
        return $this->root . ($this->root && $path ? '/' : '') . $path;
    }

    /**
     * Validates a URL path
     *
     * Path must be usable as a fragment of a URL path.
     * For interoperability and security purposes, no uppercase or "upper directory" paths like "." or ".."
     *
     * @param string $urlPath
     * @return void
     * @throws \InvalidArgumentException
     */
    private function assertUrlPath(string $urlPath): void
    {
        if (!preg_match('/^([a-z0-9_]+[a-z0-9\._]*(\/[a-z0-9_]+[a-z0-9\._]*)*)?$/', $urlPath)) {
            throw new \InvalidArgumentException(
                "URL path must be relative directory path in lowercase with '/' directory separator: '{$urlPath}'"
            );
        }
    }

    public function getPath(Path $path): string
    {
        if (!isset($this->directories[$path->name][self::PATH])) {
            throw new \Exception('Not defined');
        }
        return $this->directories[$path->name][self::PATH];
    }

    public function getUrlPath(Path $path): string
    {
        if (!isset($this->directories[$path->name][self::URL_PATH])) {
            throw new \Exception('Not defined');
        }
        return $this->directories[$path->name][self::URL_PATH];
    }
}
