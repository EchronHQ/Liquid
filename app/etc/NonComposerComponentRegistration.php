<?php
declare(strict_types=1);

/**
 * Register components (via a list of glob patterns)
 */

namespace Liquid\NonComposerComponentRegistration;

use RuntimeException;


/**
 * Include files from a list of glob patterns
 */
(static function (): void {
    $vendorPath = 'vendor/echron/liquid/';
    $globPatterns = [

//        'app/code/*/*/cli_commands.php',
        'app/code/*/*/registration.php',
        'app/design/*/*/*/registration.php',
        'app/i18n/*/*/registration.php',
//    'lib/internal/*/*/registration.php',
//    'lib/internal/*/*/*/registration.php',
//    'setup/src/*/*/registration.php',
//        $vendorPath . 'app/code/*/*/cli_commands.php',
        $vendorPath . 'app/code/*/*/registration.php',
        $vendorPath . 'app/design/*/*/*/registration.php',
        $vendorPath . 'app/i18n/*/*/registration.php',


    ];
    $baseDir = \dirname(__DIR__, 2) . '/';
    foreach ($globPatterns as $globPattern) {
        // Sorting is disabled intentionally for performance improvement
        $files = \glob($baseDir . $globPattern, GLOB_NOSORT);
        if ($files === false) {
            throw new RuntimeException("glob(): error with '$baseDir$globPattern'");
        }
        \array_map(
            static function (string $file): void {
                require_once $file;
            },
            $files
        );
    }
})();
