<?php

namespace Base\Wikidoc\Tests\DependencyInjection;

use Base\Wikidoc\Bridge\Symfony\Controller\Backend\ManualController as BridgeBackOfficeManualController;
use Base\Wikidoc\Bridge\Symfony\DependencyInjection\WikidocExtension as BridgeWikidocExtension;
use Base\Wikidoc\Bridge\Symfony\Site\Controller\ManualController as BridgeManualController;
use Base\Wikidoc\Bridge\Symfony\WikidocBundle as BridgeWikidocBundle;
use Base\Wikidoc\Controller\Backend\ManualController as BackOfficeManualController;
use Base\Wikidoc\DependencyInjection\WikidocConfiguration;
use Base\Wikidoc\DependencyInjection\WikidocExtension;
use Base\Wikidoc\Documentation\DocumentationRegistry;
use Base\Wikidoc\Manual\ManualRegistry;
use Base\Wikidoc\Search\ManualSearch;
use Base\Wikidoc\Search\TypesenseIndex;
use Base\Wikidoc\Site\Controller\ManualController;
use Base\Wikidoc\WikidocBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Routing\Loader\AttributeDirectoryLoader;
use Typesense\Bundle\ORM\TypesenseManager;

final class ExtensionTest extends TestCase
{
    /** What chapaland and La Touche Originale have: the back office's roots, or nothing at all. */
    private const BEFORE = ['roots' => [
        'base' => ['path' => '%kernel.project_dir%/vendor/glitchr/omnibase/docs', 'label' => 'Base'],
        'app' => ['path' => '%kernel.project_dir%/docs', 'label' => 'Application'],
    ]];

    /** @param array<string, mixed> $config */
    private function load(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', '/srv/app');
        (new WikidocExtension())->load([$config], $container);

        return $container;
    }

    public function testASiteThatSaysNothingNewGetsNothingNew(): void
    {
        foreach ([self::BEFORE, []] as $config) {
            $container = $this->load($config);

            self::assertFalse($container->hasDefinition(ManualController::class), 'no public controller');
            self::assertFalse($container->hasDefinition(TypesenseIndex::class), 'no engine');
            self::assertFalse($container->getParameter('wikidoc.public.enabled'));
            self::assertFalse($container->getParameter('wikidoc.search.typesense.enabled'));
            self::assertSame([], $container->getDefinition(ManualRegistry::class)->getArgument('$configured'));
            self::assertSame([], $container->getDefinition(ManualRegistry::class)->getArgument('$discover'));
        }

        // The back office's manual is wired as it was.
        $container = $this->load(self::BEFORE);
        self::assertSame([
            'base' => ['path' => '/srv/app/vendor/glitchr/omnibase/docs', 'label' => 'Base'],
            'app' => ['path' => '/srv/app/docs', 'label' => 'Application'],
        ], $container->getDefinition(DocumentationRegistry::class)->getArgument('$roots'));
        if (class_exists(\Base\Admin\Context\AdminContext::class)) {
            self::assertTrue($container->hasDefinition(BackOfficeManualController::class));
        }
    }

    /**
     * The sites import folders, not classes (config/routes.yaml): @WikidocBundle/src/Controller, whole,
     * for the back office's manual, and @WikidocBundle/src/Site/Controller for the public manuals. Since
     * step C8 the controllers are the bridge's (src/Bridge/Symfony); the classes those folders hold
     * inherit their routes, read here as Symfony reads them.
     */
    public function testTheFoldersTheSitesImportCarryTheirRoutes(): void
    {
        $routes = static function (string $folder): array {
            $collection = (new AttributeDirectoryLoader(new FileLocator(), new AttributeRouteControllerLoader()))->load(\dirname(__DIR__, 2).'/'.$folder, 'attribute');
            $routes = [];
            foreach ($collection->all() as $name => $route) {
                $routes[$name] = [$route->getPath(), $route->getDefault('_controller')];
            }
            ksort($routes);

            return $routes;
        };

        // The older sites' folder: the back office's manual, nothing public.
        self::assertSame([
            'backoffice_manual' => ['/docs/{path}', BackOfficeManualController::class.'::index'],
            'backoffice_manual_search' => ['/docs/_search', BackOfficeManualController::class.'::search'],
        ], $routes('src/Controller'));
        self::assertSame([
            'backoffice_manual' => ['/docs/{path}', BridgeBackOfficeManualController::class.'::index'],
            'backoffice_manual_search' => ['/docs/_search', BridgeBackOfficeManualController::class.'::search'],
        ], $routes('src/Bridge/Symfony/Controller'));
        self::assertTrue(is_subclass_of(BackOfficeManualController::class, BridgeBackOfficeManualController::class));

        // The public routes, under the configurable prefix.
        $public = [
            'wikidoc_asset' => ['/%wikidoc.public.path%/_file/{path}', 'asset'],
            'wikidoc_index' => ['/%wikidoc.public.path%', 'index'],
            'wikidoc_manual' => ['/%wikidoc.public.path%/{path}', 'page'],
            'wikidoc_search' => ['/%wikidoc.public.path%/_search', 'search'],
        ];
        foreach (['src/Site/Controller' => ManualController::class, 'src/Bridge/Symfony/Site/Controller' => BridgeManualController::class] as $folder => $class) {
            self::assertSame(array_map(static fn (array $route): array => [$route[0], $class.'::'.$route[1]], $public), $routes($folder), $folder);
        }
    }

    /** Each controller is a service under both names, and the public one goes under both unless asked for. */
    public function testTheControllersAreServicesUnderBothNames(): void
    {
        $container = $this->load(self::BEFORE + ['public' => ['enabled' => true]]);
        self::assertTrue($container->hasDefinition(ManualController::class));
        self::assertTrue($container->hasDefinition(BridgeManualController::class));
        self::assertTrue($container->getDefinition(ManualController::class)->hasTag('controller.service_arguments'));
        if (class_exists(\Base\Admin\Context\AdminContext::class)) {
            self::assertTrue($container->hasDefinition(BackOfficeManualController::class));
            self::assertTrue($container->hasDefinition(BridgeBackOfficeManualController::class));
        }

        $container = $this->load(self::BEFORE);
        self::assertFalse($container->hasDefinition(ManualController::class));
        self::assertFalse($container->hasDefinition(BridgeManualController::class));
    }

    /** The names the sites and their tests knew still answer (plan "omnibase agnostique", D6). */
    public function testTheKeptNames(): void
    {
        self::assertSame(BridgeWikidocExtension::class, (new \ReflectionClass(WikidocExtension::class))->getName());
        self::assertSame(\Base\Wikidoc\Bridge\Symfony\DependencyInjection\WikidocConfiguration::class, (new \ReflectionClass(WikidocConfiguration::class))->getName());
        self::assertSame(\Base\Wikidoc\Bridge\Symfony\Command\IndexCommand::class, (new \ReflectionClass(\Base\Wikidoc\Command\IndexCommand::class))->getName());
        self::assertSame(\Base\Wikidoc\Bridge\Symfony\Command\SyncCommand::class, (new \ReflectionClass(\Base\Wikidoc\Command\SyncCommand::class))->getName());
        self::assertSame(\Base\Wikidoc\Bridge\Symfony\Search\BundleTypesenseClient::class, (new \ReflectionClass(\Base\Wikidoc\Search\BundleTypesenseClient::class))->getName());

        // The bundle a site registers: its name, its folder (omnibase's bundle scan reads src/), its root,
        // and the extension beside the bridge's class.
        $bundle = new WikidocBundle();
        self::assertInstanceOf(BridgeWikidocBundle::class, $bundle);
        self::assertSame('WikidocBundle', $bundle->getName());
        self::assertSame(\dirname(__DIR__, 2), $bundle->getPath());
        self::assertSame(\dirname(__DIR__, 2).'/src', \dirname((string) (new \ReflectionClass($bundle))->getFileName()));
        self::assertInstanceOf(BridgeWikidocExtension::class, $bundle->getContainerExtension());
        self::assertSame('wikidoc', $bundle->getContainerExtension()->getAlias());
        self::assertSame(\dirname(__DIR__, 2), (new BridgeWikidocBundle())->getPath());
    }

    public function testThePublicManualsOnDemand(): void
    {
        $container = $this->load(self::BEFORE + [
            'manuals' => ['glitchr/omnitrade' => ['path' => '%env(OMNI_REPOS)%/omnitrade/core', 'versions' => ['1.x' => '/srv/exports/1.x']]],
            'discover' => ['%env(OMNI_REPOS)%/*/*'],
            'discover_exclude' => ['acme/*'],
            'cache_ttl' => 120,
            'public' => ['enabled' => true, 'path' => 'manuals'],
            'search' => ['typesense' => ['enabled' => true, 'prefix' => 'omni']],
        ]);

        self::assertTrue($container->hasDefinition(ManualController::class));
        self::assertSame('manuals', $container->getParameter('wikidoc.public.path'));

        $registry = $container->getDefinition(ManualRegistry::class);
        // The package's name is kept as written; the env placeholder is left for the running container.
        self::assertSame(['glitchr/omnitrade'], array_keys($registry->getArgument('$configured')));
        self::assertSame(['1.x' => ['path' => '/srv/exports/1.x', 'branch' => null]], $registry->getArgument('$configured')['glitchr/omnitrade']['versions']);
        self::assertSame(['%env(OMNI_REPOS)%/*/*'], $registry->getArgument('$discover'));
        self::assertSame(120, $registry->getArgument('$options')['cache_ttl']);
        self::assertSame(['acme/*'], $registry->getArgument('$options')['exclude']);
        self::assertSame('/srv/app/var/wikidoc', $container->getParameterBag()->resolveValue($registry->getArgument('$options')['export_dir']));

        if (class_exists(TypesenseManager::class)) {
            self::assertTrue($container->hasDefinition(TypesenseIndex::class));
            self::assertSame('omni', $container->getDefinition(TypesenseIndex::class)->getArgument(2));
        } else {
            self::assertFalse($container->hasDefinition(TypesenseIndex::class), 'asked for, but glitchr/typesense-bundle is not installed: the local index');
        }
        // Either way the search is there, and takes the engine only if there is one.
        self::assertEquals(new Reference(TypesenseIndex::class, ContainerBuilder::NULL_ON_INVALID_REFERENCE), $container->getDefinition(ManualSearch::class)->getArgument(2));
    }

    public function testDefaults(): void
    {
        $config = (new Processor())->processConfiguration(new WikidocConfiguration(), []);

        self::assertSame([], $config['roots']);
        self::assertSame([], $config['manuals']);
        self::assertSame([], $config['discover']);
        self::assertSame(['enabled' => false, 'path' => 'docs'], $config['public']);
        self::assertSame(['enabled' => false, 'connection' => null, 'prefix' => 'wikidoc'], $config['search']['typesense']);
        self::assertSame('docs', $config['docs']);
        self::assertSame('README.md', $config['readme']);
        self::assertSame(0, $config['cache_ttl']);
    }

    public function testAPublicPathWithSlashesAroundIsRefused(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        (new Processor())->processConfiguration(new WikidocConfiguration(), [['public' => ['path' => '/docs/']]]);
    }
}
