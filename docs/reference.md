---
title: Reference
order: 70
---

# Reference

## Routes

| Route | Path | Methods | Controller | Imported from |
|---|---|---|---|---|
| `backoffice_manual_search` | `/docs/_search` | GET | `Bridge\Symfony\Controller\Backend\ManualController::search` (priority 10) | `@WikidocBundle/src/Controller` |
| `backoffice_manual` | `/docs/{path}` (`path`: optional, `.+`) | any | `Bridge\Symfony\Controller\Backend\ManualController` | `@WikidocBundle/src/Controller` |
| `wikidoc_index` | `/<public.path>` | GET | `Bridge\Symfony\Site\Controller\ManualController` | `@WikidocBundle/src/Site/Controller` |
| `wikidoc_search` | `/<public.path>/_search` | GET | the same (priority 10) | |
| `wikidoc_asset` | `/<public.path>/_file/{path}` | GET | the same (priority 10) | |
| `wikidoc_manual` | `/<public.path>/{path}` | GET | the same | |

`<public.path>` is `wikidoc.public.path` (`docs`). The back office's controller is
registered only when omnibase/admin is installed (it renders in its layout, with
its `AdminContext` and `MenuBuilder`); the public one only with
`wikidoc.public.enabled`. Both share `/docs` by default: a site that publishes its
manuals and has the back office's manual gives `public.path` another value, or
imports the two under different prefixes.

## Console commands

```
Description:
  Copies the documentation of every version branch of the manuals' repositories

Usage:
  wikidoc:sync [options] [--] [<manual>]

Arguments:
  manual                One manual, by its package name (default: all of them)

Options:
      --remote=REMOTE   The remote whose branches are read beside the local ones [default: "origin"]
```

```
Description:
  Writes the manuals' search index into Typesense

Usage:
  wikidoc:index [<manual>]

Arguments:
  manual                One manual, by its package name (default: all of them)
```

What each does is in [Public manuals](manuals.md#versions) (`wikidoc:sync`) and
[Search](search.md#typesense) (`wikidoc:index`). Run in the core's harness, where no
manual is configured and Typesense is off, they answer:

```
$ php bin/console wikidoc:sync

 ! [NOTE] No manual is configured (wikidoc.manuals, wikidoc.discover).

$ php bin/console wikidoc:index

 ! [NOTE] Typesense is not switched on (wikidoc.search.typesense.enabled,
 !        glitchr/typesense-bundle): the search reads the local index - 0
 !        record(s) in 0 manual(s).
```

## Services

`config/services.php` registers `DocumentationRegistry` (its roots from
`wikidoc.roots`), `MarkdownRenderer`, `SearchIndexBuilder`, `ManualRegistry`,
`ManualMarkdownRenderer`, `PageRenderer` (its `$editPattern` from `wikidoc.edit_url`),
`ManualIndex`, `LocalSearch`, `ManualSearch`, `BranchExporter`, the two commands
and the controllers. With `search.typesense.enabled` and glitchr/typesense-bundle
installed, the extension adds `Bridge\Symfony\Search\BundleTypesenseClient` (on
`typesense_manager` and the connection named), aliased as
`Base\Wikidoc\Search\TypesenseClientInterface`, and `Search\TypesenseIndex`; without
them `ManualSearch` has no engine and the local index answers.

| Class | What it is |
|---|---|
| `Documentation\DocumentationRegistry` | The back office's roots and pages: `getTree()`, `getSequence()`, `getDefault()`, a page by its path |
| `Documentation\DocPage` | A page: its path, title, order, file, the root it comes from, the page it supersedes |
| `Documentation\MarkdownRenderer` | A page's HTML (league/commonmark, GFM, callouts, raw HTML escaped) and its plain text |
| `Documentation\SearchIndexBuilder` | The back office's search records |
| `Manual\ManualRegistry` | The public manuals: configured and discovered, their versions, their pages |
| `Manual\Manual`, `ManualVersion`, `ManualLocation` | A manual (`name`, `label`, `path`, `versions`, `defaultVersion`, `repository`, `urlPath()`), a version (`name`, `branch`, `root`, `live`: a checkout, or an export of `wikidoc:sync`), a page's place in a version (`manual`, `version`, `path`) |
| `Manual\PageRenderer`, `RenderedPage` | A page rendered: `html`, `headings`, `titled` (whether it has a title of its own), `editUrl` ("Edit this page"), `sourceUrl`, `source` |
| `Manual\ManualMarkdownRenderer` | The manuals' Markdown, links and pictures rewritten |
| `Manual\BranchExporter`, `Git` | `wikidoc:sync`'s copy of a branch's `docs/` and README |
| `Search\ManualSearch`, `LocalSearch`, `ManualIndex`, `TypesenseIndex` | The search, the local index, the records, the engine |
| `Search\SearchResult`, `SearchHit` | A result (`engine`, `hits`), a hit (`path`, `title`, `section`, `anchor`) |
| `Search\TypesenseClientInterface` | What `TypesenseIndex` asks of a Typesense client |
