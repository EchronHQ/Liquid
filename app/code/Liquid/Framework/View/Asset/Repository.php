<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Asset;

use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Design\Theme;

class Repository
{
    private array|null $defaults = null;

    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {
    }

    /**
     * Update required parameters with default values if custom not specified
     *
     * @param array $params
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \UnexpectedValueException
     */
    public function updateDesignParams(array $params): array
    {
        // Set area
        if (empty($params['area'])) {
            $params['area'] = $this->getDefaultParameter('area');
        }

        // Set themeModel
        $theme = null;
        /** @var AreaCode $area */
        $area = $params['area'];
        if (!empty($params['themeId'])) {
            $theme = $params['themeId'];
        } elseif (isset($params['theme'])) {
            $theme = $params['theme'];
        } elseif (empty($params['themeModel']) && $area !== $this->getDefaultParameter('area')) {
            // $theme = $this->design->getConfigurationDesignTheme($area);
            // TODO: get this from configuration
            $theme = 'Attlaz_Default';
        }


        if ($theme !== null) {
            // TODO: implement to fetch theme data
            $themeProvider = $this->objectManager->get(Theme\ThemeProvider::class);
            if (is_numeric($theme) || is_string($theme)) {
                $params['themeModel'] = $themeProvider->getThemeById($theme);
            } else {
                $params['themeModel'] = $themeProvider->getThemeByFullPath($area->value . '/' . $theme);
            }

            if (!$params['themeModel']) {
                throw new \UnexpectedValueException("Could not find theme " . $theme . " for area " . $area->value . "");
            }
        } elseif (empty($params['themeModel'])) {
            $params['themeModel'] = $this->getDefaultParameter('themeModel');
        }

        // Set module
        if (!array_key_exists('module', $params)) {
            $params['module'] = false;
        }

        // Set locale
        if (empty($params['locale'])) {
            $params['locale'] = $this->getDefaultParameter('locale');
        }
        return $params;
    }

    /**
     * Get default design parameter
     *
     * @param string $name
     * @return mixed
     */
    private function getDefaultParameter(string $name): mixed
    {

        // $this->defaults = $this->design->getDesignParams();
        return $this->defaults[$name] ?? null;
    }
}
