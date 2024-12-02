<?php
declare(strict_types=1);

use Liquid\Blog\Controller\Blog\Category;
use Liquid\Blog\Controller\Blog\Overview as BlogOverview;
use Liquid\Blog\Controller\Blog\Tag;
use Liquid\Blog\Controller\Post\View as BlogPostView;
use Liquid\Blog\Controller\Term\View as BlogTermView;

//$name = 'Liquid_Blog';
return [
    'router' => [
        'id' => 'standard',
        'routes' => [
            'blog' => [
                '' => BlogOverview::class,
                ':postId' => BlogPostView::class,
                'category/:categoryId' => Category::class,
                'tag/:tagId' => Tag::class,
                'term/:termId' => BlogTermView::class,
            ],
        ],
    ],
];
//$viewableEntityRepositories = [
//    BlogRepository::class,
//    TerminologyRepository::class,
//];
