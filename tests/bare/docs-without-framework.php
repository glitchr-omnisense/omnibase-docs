<?php

/*
 * omnibase/docs where there is no framework: run by omniscient/omnibase's
 * docker/bin/bare with Composer's autoloader alone (tests/BareTest.php), it
 * builds the services as another framework would - by hand, from the bundle's
 * root namespace - over the fixtures' repositories, and returns what they
 * answer: the manuals found, a page rendered with its links, a local search,
 * the back office's pages of a root. Nothing of the framework may be loaded.
 */

use Base\Wikidoc\Documentation\DocumentationRegistry;
use Base\Wikidoc\Documentation\MarkdownRenderer;
use Base\Wikidoc\Manual\ManualMarkdownRenderer;
use Base\Wikidoc\Manual\ManualRegistry;
use Base\Wikidoc\Manual\PageRenderer;
use Base\Wikidoc\Search\LocalSearch;
use Base\Wikidoc\Search\ManualIndex;
use Base\Wikidoc\Search\ManualSearch;

$fixtures = dirname(__DIR__).'/Fixtures';

$manuals = new ManualRegistry([], [$fixtures.'/repos/*/*'], ['export_dir' => $fixtures.'/export']);
$widget = $manuals->get('acme/widget');
$version = $widget->getVersion(null);
$page = (new PageRenderer($manuals, new ManualMarkdownRenderer()))->render(
    $widget,
    $version,
    $manuals->pages($widget, $version)->get('installation'),
    static fn (string $target, ?string $anchor = null): string => '/docs/'.$widget->urlPath($version->name, $target).($anchor ? '#'.$anchor : ''),
    static fn (string $file): string => '/docs/_file/'.$widget->urlPath($version->name, $file),
);

$search = new ManualSearch($manuals, new LocalSearch(new ManualIndex($manuals, new MarkdownRenderer())));
$found = $search->search('signature', 'acme/widget');

$pages = new DocumentationRegistry(['widget' => ['path' => $fixtures.'/repos/acme/widget/docs', 'label' => 'Widget']]);

return [
    'manuals' => array_map(static fn ($manual): string => $manual->name, array_values($manuals->all())),
    'link' => str_contains($page->html, '<a href="/docs/acme/widget/current/20-guides/webhooks">configure the webhooks</a>'),
    'picture' => str_contains($page->html, '<img src="/docs/_file/acme/widget/current/docs/img/flow.svg"'),
    'engine' => $found->engine,
    'hit' => [$found->hits[0]->path, $found->hits[0]->title, $found->hits[0]->section, $found->hits[0]->anchor],
    'pages' => array_map(static fn ($page): string => $page->path, $pages->getSequence()),
];
