<?php
declare(strict_types=1);

use Liquid\Content\Controller\Contact\Submit as SubmitContact;
use Liquid\Content\Controller\Demo\Submit as SubmitDemo;
use Liquid\Content\Controller\Page\View as PageViewController;
use Liquid\Core\Controller\PageNotFoundController;

die('This is no longer used');
return [
    'router' => [
        'id' => 'standard',
        'controllers' => [

        ],
        'routes' => [
            'content' => [
                'page/view' => PageViewController::class,
                'noroute/index' => PageNotFoundController::class,
            ],
            'demo' => [
                'submit' => SubmitDemo::class,
            ],
            'contact' => [
                'submit' => SubmitContact::class,
            ],
            'pagenotfound' => [
                '' => PageNotFoundController::class,
            ],

        ],
    ],

];
//$viewableEntityRepositories = [
//    PageRepository::class,
//];
