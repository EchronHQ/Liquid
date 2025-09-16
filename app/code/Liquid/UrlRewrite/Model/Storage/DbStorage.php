<?php
declare(strict_types=1);

namespace Liquid\UrlRewrite\Model\Storage;

use Liquid\UrlRewrite\Model\Resource\UrlRewrite;
use Liquid\UrlRewrite\Model\UrlFinderInterface;

class DbStorage implements UrlFinderInterface
{
    public const string TABLE_NAME = 'url_rewrite';

    /**
     * @inheritdoc
     */
    public function findOneByData(array $data): UrlRewrite|null
    {
        // TODO: Implement findOneByRequestPath() method.
        return null;
    }

    /**
     * @inheritdoc
     */
    public function findAllByData(array $data): array
    {
        // TODO: Implement findAllByRequestPath() method.
        return [];
    }
}
