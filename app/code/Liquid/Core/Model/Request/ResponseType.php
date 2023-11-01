<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Request;

enum ResponseType
{
    case Html;
    case Xml;
    case Json;
    case Redirect;
}
