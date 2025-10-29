<?php

declare(strict_types=1);

namespace Liquid\UrlRewrite\Model\Resource;

use Liquid\Framework\AbstractSimpleObject;

class UrlRewrite extends AbstractSimpleObject
{
    public const string ENTITY_ID = 'entity_id';
    public const string ENTITY_TYPE = 'entity_type';
    public const string REQUEST_PATH = 'request_path';
    public const string TARGET_PATH = 'target_path';
    public const string SEGMENT_ID = 'segment_id';
    public const string REDIRECT_TYPE = 'redirect_type';

    public function __construct(string|null $request = null, string|null $target = null, UrlRewriteType $statusCode = UrlRewriteType::PERMANENT)
    {
        if ($request !== null) {
            $this->setRequestPath($request);
        }
        if ($target !== null) {
            $this->setTargetPath($target);
        }
        $this->setRedirectType($statusCode);
        parent::__construct([]);
    }

    public function getEntityId(): string|null
    {
        return $this->_get(self::ENTITY_ID);
    }

    public function setEntityId(string $entityId): self
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getEntityType(): string|null
    {
        return $this->_get(self::ENTITY_TYPE);
    }


    public function setEntityType(string $entityType): self
    {
        return $this->setData(self::ENTITY_TYPE, $entityType);
    }

    public function getRequestPath(): string
    {
        return $this->_get(self::REQUEST_PATH);
    }

    public function setRequestPath(string $requestPath): self
    {
        return $this->setData(self::REQUEST_PATH, \ltrim($requestPath, '/'));
    }

    public function getTargetPath(): string
    {
        return $this->_get(self::TARGET_PATH);
    }

    public function setTargetPath(string $targetPath): self
    {
        return $this->setData(self::TARGET_PATH, \ltrim($targetPath, '/'));
    }

    public function getRedirectType(): UrlRewriteType
    {
        return $this->_get(self::REDIRECT_TYPE);
    }

    public function setRedirectType(UrlRewriteType $redirectCode): self
    {
        return $this->setData(self::REDIRECT_TYPE, $redirectCode);
    }
}
