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
| `tests/Documentation/DocumentationRegistryTest.php` | The roots, the override by path, the titles and the order, the sequence |
| `tests/Manual/ManualRegistryTest.php` | Discovery, naming (an npm scope kept), versions, pages |
| `tests/Manual/GitTest.php` | The branches read with `git`, the export |
| `tests/Manual/PageRendererTest.php` | A page's HTML, its links and pictures rewritten, "Edit this page" |
| `tests/Search/ManualSearchTest.php` | The local index and the engine (`tests/Search/FakeTypesense.php` stands in for a server), the fallback when the engine does not answer |
| `tests/DependencyInjection/ExtensionTest.php` | The configuration and what the extension registers |
| `tests/BareTest.php` | The root namespace loaded with Composer's autoloader alone ([Without Symfony](without-symfony.md)) |

An application's own test of its manual can build `DocumentationRegistry` on its
`docs/` and assert that every page has a title and that `getSequence()` is in the
order it wants; a search of its own goes through `ManualSearch` with
`LocalSearch`, or a `TypesenseClientInterface` of its own in place of a server.
