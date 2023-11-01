<?php

declare(strict_types=1);

namespace Liquid\Core\Repository;

use Attlaz\Adapter\Base\RemoteService\SqlRemoteService;
use Liquid\Content\Helper\LocaleHelper;

class BaseRepository
{
    public function __construct(protected SqlRemoteService $remoteService, protected LocaleHelper $localeHelper)
    {

    }
}
