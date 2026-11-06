<?php

/*
 * This bundle's test namespace (Base\Wikidoc\Tests\: Fixtures and the like), for a run that did not go through
 * tests/bootstrap.php: an application's suite over vendor/omnibase/docs/tests (lesringostars' phpunit.xml) runs on
 * the application's autoloader, which does not know this bundle's autoload-dev, so a test naming Fixtures stopped
 * the whole run (DocumentationRegistryTest's class constant is read while PHPUnit builds the suite). Registered once,
 * beside the application's autoloader, without loading another Composer autoloader (that would change
 * Composer\InstalledVersions' root for the other packages' tests).
 */
if (!defined('BASE_WIKIDOC_TESTS_AUTOLOAD')) {
    define('BASE_WIKIDOC_TESTS_AUTOLOAD', true);
    spl_autoload_register(static function (string $class): void {
        $prefix = 'Base\\Wikidoc\\Tests\\';
        if (str_starts_with($class, $prefix) && is_file($file = __DIR__.'/'.strtr(substr($class, \strlen($prefix)), '\\', '/').'.php')) {
            require $file;
        }
    });
}
