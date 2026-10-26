<?php

namespace Base\Wikidoc\Tests;

use Base\BaseBundle;
use Base\Wikidoc\WikidocBundle;
use PHPUnit\Framework\TestCase;

/**
 * omnibase/docs outside the framework (plan "omnibase agnostique", step C8):
 * omnisense/omnibase's docker/bin/bare, run in a PHP process of its own - this
 * one has loaded PHPUnit and the suite - requires Composer's autoloader alone,
 * loads every class of tests/Fondation.php and reports every class, interface,
 * trait and file PHP loaded on the way. None may be the framework's (what that
 * means is written at the top of docker/bin/bare and in the core's BareTest).
 */
final class BareTest extends TestCase
{
    /**
     * The former names of the classes that moved to src/Bridge/Symfony, kept for the sites: class
     * aliases, and the subclasses that the folders the sites import (src/Controller,
     * src/Site/Controller) and their config/bundles.php name. They are the bridge's, not the Fondation's.
     */
    private const KEPT = [
        'Base\Wikidoc\Command\IndexCommand',
        'Base\Wikidoc\Command\SyncCommand',
        'Base\Wikidoc\Controller\Backend\ManualController',
        'Base\Wikidoc\DependencyInjection\WikidocConfiguration',
        'Base\Wikidoc\DependencyInjection\WikidocExtension',
        'Base\Wikidoc\Search\BundleTypesenseClient',
        'Base\Wikidoc\Site\Controller\ManualController',
        'Base\Wikidoc\WikidocBundle',
    ];

    public function testTheFondationLoadsWithNoClassNorFileOfTheFramework(): void
    {
        $fondation = require __DIR__.'/Fondation.php';
        [$status, $report] = self::bare($fondation);

        self::assertSame($fondation, array_keys($report['classes']), 'every class of the Fondation was loaded');
        self::assertSame([], $report['baseline']['symbols'], 'the autoloader alone loads no class of the framework');

        $failures = [];
        foreach ($report['classes'] as $class => $result) {
            if (null !== $result['error']) {
                $failures[] = $class.' does not load: '.$result['error'];
            }
            foreach ($result['framework'] as $symbol => $askedBy) {
                $failures[] = $class.' loads '.$symbol.(null === $askedBy || $askedBy === $class ? '' : ', asked by '.$askedBy);
            }
            foreach ($result['framework_files'] as $file) {
                $failures[] = $class.' loads the file '.$file;
            }
        }
        self::assertSame([], $failures, "a class of the Fondation loads the framework:\n".implode("\n", $failures));
        self::assertSame(0, $status);
        self::assertCount(20, $fondation);
    }

    /** Sorted, each class once, and the whole root namespace: every class of src/ is the Fondation's, the bridge's or a kept name. */
    public function testTheFondationIsTheWholeRootNamespace(): void
    {
        $fondation = require __DIR__.'/Fondation.php';
        $sorted = $fondation;
        sort($sorted);
        self::assertSame($sorted, $fondation, 'sorted');
        self::assertSame(array_values(array_unique($fondation)), $fondation, 'each class once');

        $src = \dirname(__DIR__).'/src';
        $root = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $path = substr($file->getPathname(), \strlen($src) + 1);
            if (str_ends_with($path, '.php') && !str_starts_with($path, 'Bridge/') && !str_starts_with($path, 'Resources/')) {
                $root[] = 'Base\\Wikidoc\\'.str_replace('/', '\\', substr($path, 0, -4));
            }
        }
        sort($root);
        self::assertSame([], array_values(array_diff($root, self::KEPT, $fondation)), 'a class of the root namespace is neither in the Fondation nor a kept name');
        self::assertSame([], array_values(array_diff($fondation, $root)), 'a class of the Fondation is not in src/ outside the bridge');
        foreach (self::KEPT as $class) {
            self::assertStringStartsWith('Base\\Wikidoc\\Bridge\\Symfony\\', self::bridgeName($class), $class.' is the bridge\'s');
        }
    }

    /**
     * The check is not blind: the same report, for the bundle a site registers, names the framework
     * (HttpKernel's Bundle) and who asked for it.
     */
    public function testAClassOfTheFrameworkIsNamed(): void
    {
        [$status, $report] = self::bare([WikidocBundle::class]);

        self::assertSame(1, $status);
        $bundle = $report['classes'][WikidocBundle::class];
        self::assertNull($bundle['error']);
        self::assertSame('Base\\Bundle\\AbstractBaseBundle', $bundle['framework']['Symfony\\Component\\HttpKernel\\Bundle\\Bundle'] ?? null);
        self::assertNotEmpty(preg_grep('~/symfony/http-kernel/~', $bundle['framework_files']));
    }

    /**
     * The documentation works where there is no framework: tests/bare/docs-without-framework.php,
     * run by bare with the autoloader alone, builds the services by hand over the fixtures'
     * repositories and renders a manual's page, searches it, lists a root's pages.
     */
    public function testTheDocumentationWorksWithoutTheFramework(): void
    {
        $script = __DIR__.'/bare/docs-without-framework.php';
        [$status, $report] = self::bare([$script]);

        self::assertSame(0, $status);
        $run = $report['classes'][$script];
        self::assertNull($run['error']);
        self::assertSame([], $run['framework']);
        self::assertSame([], $run['framework_files']);
        $result = $run['result'];
        self::assertContains('acme/widget', $result['manuals']);
        self::assertTrue($result['link'], 'a relative link goes to the page its file became');
        self::assertTrue($result['picture'], 'a picture of the manual is served by the site');
        self::assertSame('local', $result['engine']);
        self::assertSame(['20-guides/webhooks', 'Webhooks', 'Signature', 'signature'], $result['hit']);
        self::assertContains('installation', $result['pages']);
    }

    /** The class a kept name resolves to, or whose class it extends: the bridge's. */
    private static function bridgeName(string $class): string
    {
        $reflection = new \ReflectionClass($class);

        return $reflection->getName() === $class ? (string) $reflection->getParentClass()?->getName() : $reflection->getName();
    }

    /**
     * Runs omnisense/omnibase's docker/bin/bare apart, on the autoloader of this run, this checkout's
     * classes first (tests/bare/autoload.php).
     *
     * @param list<string> $classes
     *
     * @return array{int, array<string, mixed>} the exit status, the report (the classes asked, without the script)
     */
    private static function bare(array $classes): array
    {
        // The autoloader that knows omnibase: under symfony/phpunit-bridge's simple-phpunit (an
        // application's suite) the first ClassLoader is PHPUnit's own.
        $autoload = $bare = null;
        foreach (\Composer\Autoload\ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
            if (false !== $file = $loader->findFile(BaseBundle::class)) {
                $autoload = $vendorDir.'/autoload.php';
                $bare = \dirname((string) realpath($file), 2).'/docker/bin/bare';
                break;
            }
        }
        self::assertNotNull($autoload, 'omnisense/omnibase is installed');
        self::assertFileExists((string) $bare, 'omnisense/omnibase\'s docker/bin/bare');

        $script = __DIR__.'/bare/autoload.php';
        $output = (string) tempnam(sys_get_temp_dir(), 'bare');
        $process = proc_open([\PHP_BINARY, $bare, '--json', $script, ...$classes], [1 => ['file', $output, 'w'], 2 => ['pipe', 'w']], $pipes, null, ['OMNIBASE_AUTOLOAD' => $autoload] + getenv());
        self::assertIsResource($process);
        $err = (string) stream_get_contents($pipes[2]);
        $status = proc_close($process);
        $out = (string) file_get_contents($output);
        unlink($output);
        $report = json_decode($out, true);
        self::assertIsArray($report, 'PHP exited '.$status.': '.$err.$out);

        $first = $report['classes'][$script] ?? null;
        self::assertIsArray($first, 'the checkout\'s autoload ran first');
        self::assertNull($first['error']);
        self::assertSame([], $first['framework']);
        unset($report['classes'][$script]);

        return [$status, $report];
    }
}
