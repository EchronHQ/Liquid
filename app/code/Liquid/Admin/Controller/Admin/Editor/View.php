<?php

declare(strict_types=1);

namespace Liquid\Admin\Controller\Admin\Editor;

use Liquid\Core\Model\Action\AbstractAction;
use Liquid\Core\Model\Result\Page;
use Liquid\Core\Model\Result\Result;

class View extends AbstractAction
{
    /**
     * http://localhost:8080/dkdkd/admin/editor/view
     */


    public function execute(): Result
    {

        //        $builder = new \PHPageBuilder\PHPageBuilder([]);
        //        $builder->handleRequest();





        return $this->getResultFactory()->create(Page::class);
    }
}
