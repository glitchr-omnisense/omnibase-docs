---
title: Without Symfony
order: 15
---

# Without Symfony

The documentation itself loads without the framework: the bundle's root
namespace - `Base\Wikidoc\Documentation` (the roots, their pages, the Markdown),
`Base\Wikidoc\Manual` (the public manuals, their versions, the export of a branch)
and `Base\Wikidoc\Search` (the search, its index, the Typesense client's
interface) - needs PHP, league/commonmark, Symfony's standalone Finder and
Filesystem (and Process for an export), a PSR-6 pool if one is given, nothing
else. `tests/BareTest.php` loads its classes (`tests/Fondation.php`) with Composer's
autoloader alone and fails if a class or a file of the framework comes with them.

## The services by hand

```php
use Base\Wikidoc\Documentation\DocumentationRegistry;
use Base\Wikidoc\Documentation\MarkdownRenderer;
use Base\Wikidoc\Manual\ManualMarkdownRenderer;
use Base\Wikidoc\Manual\ManualRegistry;
use Base\Wikidoc\Manual\PageRenderer;
use Base\Wikidoc\Search\LocalSearch;
use Base\Wikidoc\Search\ManualIndex;
use Base\Wikidoc\Search\ManualSearch;

// The back office's pages of a root, and one of them in HTML.
$pages = new DocumentationRegistry(['app' => ['path' => __DIR__.'/docs', 'label' => 'Application']]);
$html = (new MarkdownRenderer())->renderFile($pages->getDefault()->file);

// The public manuals found under a folder of repositories, a page, the search.
$manuals = new ManualRegistry([], ['/srv/repos/*/*'], ['export_dir' => '/srv/exports']);
$manual = $manuals->get('glitchr/omnitrade');
$version = $manual->getVersion(null);
$page = (new PageRenderer($manuals, new ManualMarkdownRenderer()))->render(
    $manual, $version, $manuals->pages($manual, $version)->get('installation'),
    fn (string $target, ?string $anchor = null) => '/docs/'.$manual->urlPath($version->name, $target).($anchor ? '#'.$anchor : ''),
    fn (string $file) => '/docs/_file/'.$manual->urlPath($version->name, $file),
);
$hits = (new ManualSearch($manuals, new LocalSearch(new ManualIndex($manuals, new MarkdownRenderer()))))->search('webhook');
```

`ManualRegistry`'s three arguments are what `wikidoc.manuals`, `wikidoc.discover`
and the other options give it under Symfony ([Configuration reference](configuration.md)):
the manuals named, the glob patterns to discover, and `export_dir`, `branches`,
`docs`, `readme`, `remote`, `cache_ttl`, `exclude`. `PageRenderer::render()` takes two
closures, the address of a page (with its anchor) and the address of a file of the
repository: the host's routes.

## Run in the core's harness

`tests/bare/docs-without-framework.php` does the same over the tests' fixtures
(`tests/Fixtures/repos/*/*`, three repositories). Run with the harness's
autoloader and this checkout's classes (`tests/bare/autoload.php`), it returns:

```
array (
  'manuals' =>
  array (
    0 => 'acme/plain',
    1 => '@acme/script',
    2 => 'acme/widget',
  ),
  'link' => true,
  'picture' => true,
  'engine' => 'local',
  'hit' =>
  array (
    0 => '20-guides/webhooks',
    1 => 'Webhooks',
    2 => 'Signature',
    3 => 'signature',
  ),
  'pages' =>
  array (
    0 => 'installation',
    1 => '20-guides/events',
    2 => '20-guides/webhooks',
  ),
)
```

The three manuals found (an npm package keeps its scope), a relative link and a
picture of a page rewritten to the closures' addresses, a search answered by the
local index with its section and anchor, the back office's pages of a root in
reading order.

## Under Symfony

What needs Symfony is under `src/Bridge/Symfony`: the bundle, its configuration and
extension, the two controllers, the two commands and the Typesense client of
glitchr/typesense-bundle ([Reference](reference.md)). The former names still answer:
`Base\Wikidoc\WikidocBundle` in `config/bundles.php`, the folders
`@WikidocBundle/src/Controller` and `@WikidocBundle/src/Site/Controller` in the
routes, `Base\Wikidoc\DependencyInjection\*`, `Base\Wikidoc\Command\*`.
