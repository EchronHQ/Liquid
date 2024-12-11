<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Asset;

use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\Component\ComponentFile;
use Liquid\Framework\Filesystem\Path;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\Url;
use Liquid\Framework\Url\UrlType;
use Liquid\Framework\View\Asset\File\AssetFileFallbackContext;
use Liquid\Framework\View\Design\Theme;

class Repository
{
    private array|null $defaults = null;
    private array $fallbackContext = [];

    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly Url                    $baseUrl,
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
     * Create a file asset that's subject of fallback system
     *
     * @param string $fileId
     * @param array $params
     * @return AssetFile
     */
    public function createAsset(string $fileId, array $params = []): AssetFile
    {
        $this->updateDesignParams($params);
        $module = ComponentFile::extractModule($fileId);
        if ($module['moduleId'] === null && $params['module']) {
            $module['moduleId'] = $params['module'];
        }

        if (!isset($params['publish'])) {
//            $map = $this->getRepositoryFilesMap($fileId, $params);
//            if ($map) {
//                $params = array_replace($params, $map);
//            }
        }

        $isSecure = isset($params['_secure']) ? (bool)$params['_secure'] : null;
        $themePath = isset($params['theme']) ? $params['theme'] : $this->design->getThemePath($params['themeModel']);
        $context = $this->getFallbackContext(
            UrlType::STATIC,
            $isSecure,
            $params['area'],
            $themePath,
            $params['locale']
        );

//        return new AssetFile();
//        return $this->fileFactory->create(
//            [
//                'source' => $this->assetSource,
//                'context' => $context,
//                'filePath' => $filePath,
//                'module' => $module['moduleId'],
//                'contentType' => $this->assetSource->getContentType($filePath),
//            ]
//        );
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

    /**
     * Get a fallback context value object
     *
     * Create only one instance per combination of parameters
     *
     * @param UrlType $urlType
     * @param bool|null $isSecure
     * @param AreaCode $area
     * @param string $themePath
     * @param string $locale
     * @return AssetFileFallbackContext
     */
    private function getFallbackContext(UrlType $urlType, bool $isSecure, AreaCode $area, string $themePath, string $locale): AssetFileFallbackContext
    {
        $secureKey = null === $isSecure ? 'null' : (int)$isSecure;
        $baseDirType = Path::STATIC_VIEW;
        $id = implode('|', [$baseDirType, $urlType, $secureKey, $area, $themePath, $locale]);
        if (!isset($this->fallbackContext[$id])) {
            $url = $this->baseUrl->getBaseUrl(['_type' => $urlType, '_secure' => $isSecure]);
            $this->fallbackContext[$id] = new AssetFileFallbackContext($url, $area, $themePath, $locale);

//
//                $this->fallbackContextFactory->create(
//                [
//                    'baseUrl' => $url,
//                    'areaType' => $area,
//                    'themePath' => $themePath,
//                    'localeCode' => $locale
//                ]
            // );
        }
        return $this->fallbackContext[$id];
    }
}
