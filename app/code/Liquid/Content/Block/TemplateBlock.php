<?php

declare(strict_types=1);

namespace Liquid\Content\Block;

use Liquid\Content\Helper\TemplateHelper;
use Liquid\Core\Model\BlockContext;
use Liquid\Core\Model\Layout\Block;

class TemplateBlock extends Block
{
    protected string|null $template = null;

    public function __construct(
        BlockContext                    $context,
        private readonly TemplateHelper $templateHelper,
        string|null                     $template = null
    )
    {
        parent::__construct($context);
        if ($template !== null) {
            $this->template = $template;
        }
    }

    public function setTemplate(string $path): void
    {
        if (empty($path)) {
            throw new \Exception('Template path should not be empty');
        }
        $this->template = $path;
    }

    public function getTemplate(): string
    {
        if ($this->template !== null) {
            return $this->template;
        }
        if ($this->hasData('template')) {
            $template = $this->getData('template');
            if ($template !== null) {
                return $template;
            }
        }
        $this->logger->warning('[Template Block] No template defined');
        return 'empty.phtml';
    }

    public function toHtml(): string
    {
        $this->beforeToHtml();
        try {
            $params = ['module' => self::extractModuleName(\get_class($this))];
            $fullTemplatePath = $this->templateHelper->getTemplateFileName($this->getTemplate(), $params);

            return $this->renderTemplate($fullTemplatePath);
        } catch (\Throwable $ex) {
            return $this->handleUnableToRender($ex);
        }
    }


}
