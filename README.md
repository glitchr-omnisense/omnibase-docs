# omnibase/docs

The documentation of a site, read from Markdown files: nothing is kept in a
database, a page is a file, versioned with the code it describes.

- **A manual in the back office** (`/docs`, with omnibase/admin): the
  bundle's and the application's pages, the application overriding the
  bundle's by path, a search palette.
- **Public manuals**, the way symfony.com/doc publishes its own: one manual
  per package, read in the `docs/` folder and the README of its repository,
  one version per branch; contents, breadcrumb, previous and next, the
  page's own headings, anchors, code blocks, callouts, relative links that
  follow, "Edit this page" to the file on its forge.
- **A search** over the public manuals: Typesense (a collection per manual
  and version) through glitchr/typesense-bundle, or the pages' own index
  when the engine is off or does not answer.

The public manuals and the engine are off until a site asks for them: a site
that only has the back office's manual changes nothing.

```sh
composer require omnibase/docs
```

```php
// config/bundles.php
Base\Wikidoc\WikidocBundle::class => ['all' => true],
```

The bundle's namespace and its configuration key are still `Wikidoc`
(`Base\Wikidoc\`, `wikidoc:`), from the package it was before.

## Without Symfony

The documentation itself loads without the framework (plan "omnibase
agnostique", step C8): the bundle's root namespace - `Documentation`
(the roots, their pages, the Markdown), `Manual` (the public manuals, their
versions, the export of a branch) and `Search` (the search, its index, the
Typesense client's interface) - needs PHP, league/commonmark, Symfony's
standalone Finder (and Process for an export), a PSR-6 pool if you give one,
nothing else. `tests/BareTest.php` loads its 20 classes (`tests/Fondation.php`)
with Composer's autoloader alone and fails if a class or a file of the
framework comes with them.

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

`tests/bare/docs-without-framework.php` does the same over the tests'
fixtures, run by BareTest with the autoloader alone.

What needs Symfony is under `src/Bridge/Symfony`: the bundle, its
configuration and extension, the two controllers, the two commands and the
Typesense client of glitchr/typesense-bundle. The former names still answer
(`Base\Wikidoc\WikidocBundle` in `config/bundles.php`, the folders
`@WikidocBundle/src/Controller` and `@WikidocBundle/src/Site/Controller` in
the routes, `Base\Wikidoc\DependencyInjection\*`, `Base\Wikidoc\Command\*`):
nothing changes for a site.

## Documentation

- [The back office's manual](docs/back-office.md)
- [Public manuals](docs/manuals.md): configuration, versions, addresses
- [Writing pages](docs/writing.md): titles, order, links, callouts, code
- [Search](docs/search.md): Typesense, the local index, the commands
- [Templates and look](docs/templates.md)

## Tests

```sh
# in omnisense/omnibase's harness (core/docker)
docker compose -f compose.yml -f compose.checkouts.yml run --rm omnibase test docs
# or from an application that has the bundle
php vendor/bin/phpunit -c vendor/omnibase/docs/phpunit.xml.dist
```

## Licence

MIT since 2026-10-09; earlier versions remain published under LGPL-3.0-or-later.
