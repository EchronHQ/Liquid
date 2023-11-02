<?php

use Liquid\Content\Controller\Contact\Submit as SubmitContact;
use Liquid\Content\Controller\Demo\Submit as SubmitDemo;
use Liquid\Content\Controller\Page\View as PageViewController;
use Liquid\Core\Controller\PageNotFoundController;


$routes = [
    'content' => [
        'page/view' => PageViewController::class,
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
];