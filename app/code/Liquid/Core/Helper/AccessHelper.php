<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use Liquid\Framework\App\Request\HttpRequest;

class AccessHelper
{
    public static function hasAccess(HttpRequest $request): bool
    {

        //        if ($request->getArea() === Area::Frontend) {
        //            return true;
        //        }
        return true;
        //        $nadya = '81.104.115.12';
        //        $chantrey = '82.30.226.152';
        //        $kerkwegel = '178.117.207.161';
        //        $webDev = '107.178.231.236';
        //        $copy = '84.71.84.180';
        //
        //        $allowedIps = ['172.23.0.1', '86.3.238.175', $chantrey, $kerkwegel, $copy];
        //
        //        if (\in_array($request->getIp(), $allowedIps)) {
        //            return true;
        //        }
        ////        $userAgent = $request->getHeader('User-Agent');
        ////        if (StringHelper::contains($userAgent, 'Chrome-Lighthouse')) {
        ////            return true;
        ////        }
        //        return false;
    }
}
