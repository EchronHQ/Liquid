<?php

declare(strict_types=1);

namespace Liquid\Core\Repository;

use Liquid\Content\Helper\LocaleHelper;
use Liquid\Framework\Database\Sql\Sql;
use Liquid\Framework\Database\Sql\SqlFactory;

class BaseRepository
{

    private Sql|null $remoteService = null;

    public function __construct(protected SqlFactory $sqlFactory, protected LocaleHelper $localeHelper)
    {

    }

    protected function getRemoteService(): Sql
    {
        if ($this->remoteService === null) {
            $this->remoteService = $this->sqlFactory->create();
        }
        return $this->remoteService;
    }
}
