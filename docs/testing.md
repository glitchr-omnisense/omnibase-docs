---
title: Testing
order: 80
---

# Testing

```sh
# in omnisense/omnibase's harness (core/docker)
docker compose -f compose.yml -f compose.checkouts.yml run --rm omnibase test docs
# or from an application that has the bundle
php vendor/bin/phpunit -c vendor/omnibase/docs/phpunit.xml.dist
```

The suite works on fixtures: `tests/Fixtures/repos/acme/{plain,script,widget}`, three
repositories (a README only, an npm package, a manual with guides, a picture and
versions), and `tests/Fixtures/export`, the copies `wikidoc:sync` would make.

| Test | What it checks |
|---|---|
| `tests/Documentation/DocumentationRegistryTest.php` | The roots, a home file outside the folder, the reading order, a page's neighbours and ancestors |
| `tests/Manual/ManualRegistryTest.php` | Discovery and exclusion, a manual found by its package name however spelt, a configured manual over a discovered one, the versions (the working tree first, the exports, checkouts of their own, newest first), the README as home, a page's address |
| `tests/Manual/GitTest.php` | A branch, its siblings and its origin read from the `.git` folder, outside a repository, the other branches copied out as versions |
| `tests/Manual/PageRendererTest.php` | Relative links (to pages, folders, inside each version), "Edit this page", the headings' ids, callouts, code blocks, a path that never leaves the repository, the back office's renderer untouched |
| `tests/Search/ManualSearchTest.php` | The local index (sections, every word, code), a collection per manual and version replaced at each indexing, the engine (`tests/Search/FakeTypesense.php` stands in for a server), the local index when the engine is down or a manual was never indexed |
| `tests/DependencyInjection/ExtensionTest.php` | A site that sets nothing new gets nothing new, the routes of the imported folders, the controllers under both names, the kept names, the public manuals on demand, the defaults |
| `tests/BareTest.php` | The root namespace loaded with Composer's autoloader alone ([Without Symfony](without-symfony.md)) |

An application's own test of its manual can build `DocumentationRegistry` on its
`docs/` and assert that every page has a title and that `getSequence()` is in the
order it wants; a search of its own goes through `ManualSearch` with
`LocalSearch`, or a `TypesenseClientInterface` of its own in place of a server.
