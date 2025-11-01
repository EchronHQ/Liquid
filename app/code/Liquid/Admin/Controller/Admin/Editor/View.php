<?php

declare(strict_types=1);

namespace Liquid\Admin\Controller\Admin\Editor;


use Liquid\Framework\App\Action\AbstractAction;
use Liquid\Framework\Controller\AbstractResult;
use Liquid\Framework\View\Result\Page;

class View extends AbstractAction
{
    /**
     * http://localhost:8080/dkdkd/admin/editor/view
     */


    public function execute(): AbstractResult
    {

        //        $builder = new \PHPageBuilder\PHPageBuilder([]);
        //        $builder->handleRequest();

        /** @var $page Page */
        $page = $this->getResultFactory()->create(Page::class);
        return $page;
    }
}
