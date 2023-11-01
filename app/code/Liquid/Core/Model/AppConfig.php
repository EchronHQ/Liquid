<?php

declare(strict_types=1);

namespace Liquid\Core\Model;

use Echron\Tools\StringHelper;
use Liquid\Content\Model\Locale;
use Symfony\Component\Yaml\Yaml;

class AppConfig
{
    private array $data = [];
    private ApplicationMode|null $mode = null;

    public function isCLI(): bool
    {
        return PHP_SAPI === 'cli';
    }

    public function load(): void
    {
        $configPath = \ROOT . 'app/etc/config.yml';
        $this->data = Yaml::parseFile($configPath);

        $this->data['site_url'] = $this->automaticallyDetectSiteUrl();

        if (!$this->isCLI()) {
            $this->data['current_url'] = $this->detectCurrentUrl();
        }

        $this->data['app_url'] = 'https://app.attlaz.com/';
        $this->data['status_url'] = 'https://status.attlaz.com/';
        $this->data['documentation_url'] = 'https://docs.attlaz.com/';
        $this->data['api_reference_url'] = 'https://app.swaggerhub.com/apis-docs/Echron/attlaz-api/';
        $this->data['signup_url'] = 'https://app.attlaz.com/signup';
    }

    public function setValue(string $key, mixed $value): void
    {
        if (isset($this->data[$key])) {
            throw new \Exception('Value already exists');
        }
        $this->data[$key] = $value;
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

    public function getValueString(string $key, string|null $default = null): string
    {
        $x = $this->getValue($key, $default);
        return $x . '';
    }


    public function getMode(): ApplicationMode
    {
        if (\is_null($this->mode)) {
            if ($this->getValue('app.mode') === 'develop') {
                $this->mode = ApplicationMode::DEVELOP;
            } else {
                $this->mode = ApplicationMode::PRODUCTION;
            }
        }
        return $this->mode;
    }

    private function automaticallyDetectSiteUrl(): string
    {
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


    public function setLocale(Locale $locale, bool $defined): void
    {
        $this->data['current_locale'] = $locale;
        $this->data['current_locale_defined'] = $defined;
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
        return $this->data['current_locale_defined'] === true;
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
}
