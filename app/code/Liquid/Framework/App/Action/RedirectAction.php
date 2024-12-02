<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Action;

use Liquid\Framework\Controller\Result\Redirect;
use Liquid\Framework\Controller\ResultInterface;

class RedirectAction extends AbstractAction
{
    public function __construct(Context $context, private readonly Redirect $redirect)
    {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        return $this->redirect;
    }
}
