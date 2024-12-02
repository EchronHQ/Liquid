<?php

declare(strict_types=1);

namespace Liquid\Content\Model;

use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\View\Layout\Layout;

abstract class FrontendAction extends AbstractAction
{
    public function __construct(
        Context              $context,
        protected Layout     $layout,
        protected PageConfig $pageConfig
    )
    {
        parent::__construct($context);
    }
}
