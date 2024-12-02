<?php

declare(strict_types=1);

namespace Liquid\Admin\Controller\Admin\Editor;


use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\Controller\Result;

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
