<?php
declare(strict_types=1);
/**
 * Placeholder configuration values processor.
 * Replace placeholders in configuration with config values
 */

namespace Liquid\Content\Model\Config\Processor;

use Liquid\Framework\App\Config\Processor\PostProcessorInterface;
use Liquid\Framework\App\DeploymentConfig;
use Liquid\Framework\App\Request\HttpRequest;

class Placeholder implements PostProcessorInterface
{
    private string $urlPlaceholder = '{{base_url}}';

    public function __construct(
        private readonly HttpRequest      $request,
        private readonly DeploymentConfig $deploymentConfig
    )
    {
    }

    public function process(array $data): array
    {
        foreach ($data as $scope => &$scopeData) {
            if ($scope === 'default') {
                $scopeData = $this->processData($scopeData);
            } else {
                foreach ($scopeData as &$sData) {
                    $sData = $this->processData($sData);
                }
            }
        }

        return $data;
    }

    /**
     * Get placeholder from value
     *
     * @param string|bool $value
     * @return string|null
     */
    protected function getPlaceholder(string|bool $value): string|null
    {
        if (\is_string($value) && \preg_match('/{{(.*)}}.*/', $value, $matches)) {
            $placeholder = $matches[1];
            if ($placeholder === 'unsecure_base_url' || $placeholder === 'secure_base_url' || str_contains($value, $this->urlPlaceholder)) {
                return $placeholder;
            }
        }
        return null;
    }

    private function processData(array $data = []): array
    {
        if (empty($data)) {
            return [];
        }
        // initialize $pointer, $parents and $level variable
        \reset($data);
        $pointer = &$data;
        $parents = [];
        $level = 0;

        while ($level >= 0) {
            $current = &$pointer[\key($pointer)];
            if (\is_array($current)) {
                \reset($current);
                $parents[$level] = &$pointer;
                $pointer = &$current;
                $level++;
            } else {
                $current = $this->_processPlaceholders($current, $data);

                // move pointer of last queue layer to next element
                // or remove layer if all path elements were processed
                while ($level >= 0 && \next($pointer) === false) {
                    $level--;
                    // removal of last element of $parents is skipped here for better performance
                    // on next iteration that element will be overridden
                    $pointer = &$parents[$level];
                }
            }
        }

        return $data;
    }

    private function _processPlaceholders(string|bool $value, array $data): string|bool
    {
        $placeholder = $this->getPlaceholder($value);
        if ($placeholder) {
            $url = false;
            if ($placeholder === 'unsecure_base_url') {
                $url = $this->getValue('web/unsecure/base_url', $data);
            } elseif ($placeholder === 'secure_base_url') {
                $url = $this->getValue('web/secure/base_url', $data);
            }
            if ($url) {
                $value = \str_replace('{{' . $placeholder . '}}', $url, $value);
            } elseif (str_contains($value, $this->urlPlaceholder)) {
                $distroBaseUrl = $this->request->getDistroBaseUrl();

                $value = \str_replace($this->urlPlaceholder, $distroBaseUrl, $value);
            }

            if (null !== $this->getPlaceholder($value)) {
                $value = $this->_processPlaceholders($value, $data);
            }
        }


        return $value;
    }

    private function getValue(string $key, array $data): mixed
    {
        return $this->deploymentConfig->getValue($key);
    }
}
