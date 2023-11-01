<?php

declare(strict_types=1);

namespace Liquid\Content\Model;

use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Core\Layout;
use Liquid\Core\Model\Action\AbstractAction;
use Liquid\Core\Model\Action\Context;

abstract class FrontendAction extends AbstractAction
{
    public function __construct(
        Context              $context,
        protected Layout     $layout,
        protected PageConfig $pageConfig
    ) {
        parent::__construct($context);
    }
}
