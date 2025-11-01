<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution\Types;

use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\Component\ComponentType;
use Liquid\Framework\Exception\ContextException;
use Liquid\Framework\Filesystem\DirectoryList;
use Liquid\Framework\Filesystem\Path;
use Liquid\Framework\View\Design\Theme;

/**
 * Fallback Rule Theme
 *
 * An aggregate of a fallback rule that propagates it to every theme according to a hierarchy
 */
class ResolveThemeFiles implements ResolveTypeInterface
{
    public function __construct(
        private readonly ComponentRegistrarInterface $componentRegistrar,
        private readonly DirectoryList               $directoryList,
        private readonly Theme\ThemeProvider         $themeProvider,
        private readonly ResolveTypeInterface        $resolver
    )
    {

    }

    /**
     * Propagate an underlying fallback rule to every theme in a hierarchy: parent, grandparent, etc.
     *
     * @param array $params
     * @return array
     * @throws \InvalidArgumentException|ContextException
     */
    public function getPatternDirs(array $params): array
    {
        if (!\array_key_exists('theme', $params) || !$params['theme'] instanceof Theme) {
            throw new \InvalidArgumentException(
                'Parameter "theme" should be specified and should implement the theme interface.'
            );
        }

        $result = [];
        /** @var $theme Theme */
        $theme = $params['theme'];
        unset($params['theme']);
        while ($theme) {
            if ($theme->getFullPath()) {
                $params['theme_dir'] = $this->componentRegistrar->getPath(
                    ComponentType::Theme,
                    $theme->getCode()
                );

                $params = $this->getThemePubStaticDir($theme, $params);

                $result = \array_merge($result, $this->resolver->getPatternDirs($params));
            }
            $theme = $theme->getParentTheme();

        }
        return $result;
    }

    /**
     * Get dir of Theme that contains published static view files
     *
     * @param Theme $theme
     * @param array $params
     * @return array
     * @throws ContextException
     */
    private function getThemePubStaticDir(Theme $theme, array $params = []): array
    {
        if (empty($params['theme_pubstatic_dir'])
            && isset($params['file'])
            && \pathinfo($params['file'], PATHINFO_EXTENSION) === 'css'
        ) {
            $params['theme_pubstatic_dir'] = $this->directoryList->getPath(Path::STATIC_VIEW)
                . '/' . $theme->getArea()->value
                . '/' . $theme->getCode()
                . (isset($params['locale']) ? '/' . $params['locale'] : '');
        }

        return $params;
    }
}
