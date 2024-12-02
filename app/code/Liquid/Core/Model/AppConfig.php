<?php

declare(strict_types=1);

namespace Liquid\Core\Model;

use Echron\Tools\StringHelper;
use Liquid\Content\Model\Locale;
use Liquid\Framework\App\AppMode;

class AppConfig
{

    private array $data = [];
    private AppMode|null $mode = null;

    public function setValue(string $key, mixed $value): void
    {
        if (isset($this->data[$key])) {
            throw new \Exception('Value already exists');
        }
        $this->data[$key] = $value;
    }

    public function getValueString(string $key, string|null $default = null): string
    {
        $x = $this->getValue($key, $default);
        return $x . '';
    }

    public function getValue(string $key, string|null $default = null): mixed
    {
        $path = \explode('.', $key);
        $current = $this->data;
        foreach ($path as $segment) {
            if (isset($current[$segment])) {
                $current = $current[$segment];
            } else {
                if ($default === null) {
                    throw new \Exception('Config value "' . $key . '" not found');
                }
                return $default;
            }
        }

        return $current;
    }

    public function getValueBoolean(string $key, bool|null $default = null): bool
    {
        $x = $this->getValue($key, '');
        if ($x === '') {
            if ($default === null) {
                throw new \Exception('Config value "' . $key . '" not found');
            }
            return $default;
        }

        return (bool)$x;
    }

    public function getMode(): AppMode
    {
        if ($this->mode === null) {
            if ($this->getValue('app.mode', AppMode::Production->name) === 'develop') {
                $this->mode = AppMode::Develop;
            } else {
                $this->mode = AppMode::Production;
            }
        }
        return $this->mode;
    }

    public function setLocale(Locale $locale, bool $defined): void
    {
        $this->data['current_locale'] = $locale;
        $this->data['current_locale_defined'] = $defined;
    }

    public function hasLocales(): bool
    {
        /** TODO: determine if system has more than 1 locale enabled or if this is a single locale system */
        return false;
    }

    public function getLocale(): Locale
    {
        if (!isset($this->data['current_locale'])) {
            throw new \Exception('Locale not defined');
        }
        return $this->data['current_locale'];
    }

    public function isLocaleDefined(): bool
    {
        if ($this->isCLI()) {
            // TODO: implement locale emulation
            return false;
        }
        return isset($this->data['current_locale_defined']) && $this->data['current_locale_defined'] === true;
    }

    public function isCLI(): bool
    {
        return PHP_SAPI === 'cli';
    }

    final public function debugTranslations(): bool
    {
        return false;
        // return $this->mode === ApplicationMode::DEVELOP;
    }

    final public function debugTerms(): bool
    {
        return false;
        // return $this->mode === ApplicationMode::DEVELOP;
    }

    private function automaticallyDetectSiteUrl(): string
    {
        // TODO: this is not good as
        if ($this->isCLI()) {
            $path = getcwd();
            if ($path === '/var/www/html') {
                return 'http://localhost:8900/';
            }
            if (StringHelper::contains($path, 'girasole')) {
                return 'https://girasole.attlaz.com/';
            }
            return 'https://attlaz.com/';

        }
        $server = $_SERVER;
        if (isset($server['HTTP_HOST'])) {
            return (isset($server['HTTPS']) && $server['HTTPS'] === 'on' ? "https" : "http") . "://$server[HTTP_HOST]/";
        }
        return 'http://localhost:8900/';
    }

    private function detectCurrentUrl(): string
    {
        $server = $_SERVER;
        return (isset($server['HTTPS']) && $server['HTTPS'] === 'on' ? "https" : "http") . "://$server[HTTP_HOST]$server[REQUEST_URI]";
    }
}
