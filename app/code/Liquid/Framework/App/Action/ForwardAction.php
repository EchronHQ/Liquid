<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Action;

use Liquid\Framework\Controller\Result\Forward;
use Liquid\Framework\Controller\ResultInterface;

class ForwardAction extends AbstractAction
{
    public function __construct(Context $context, private readonly Forward $forward)
    {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $this->request->setMatched(false);
        return $this->forward;
    }
}
