<?php
declare(strict_types=1);

namespace Liquid\Framework\View\File;

use Liquid\Framework\Exception\ContextException;
use Liquid\Framework\View\Design\Theme;

class File
{

    private string|null $identifier = null;

    public function __construct(
        private readonly string      $filename,
        private readonly string|null $moduleId,
        private readonly Theme|null  $theme = null,
        private readonly bool        $isBase = false
    )
    {

    }

    public function isBase(): bool
    {
        return $this->isBase;
    }

    /**
     * Calculate unique identifier for a view file
     *
     * @return string
     * @throws ContextException
     */
    public function getFileIdentifier(): string
    {
        if (null === $this->identifier) {
            $themeToken = $this->getTheme() ? ('|theme:' . $this->theme->getFullPath()) : '';
            $scopeToken = $this->isBase ? 'base' : '';
            $this->identifier = $scopeToken . $themeToken . '|module:' . $this->getModuleId() . '|file:' . $this->getFilename();
        }
        return $this->identifier;
    }

    public function getTheme(): Theme|null
    {
        return $this->theme;
    }

    public function getModuleId(): string|null
    {
        return $this->moduleId;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
