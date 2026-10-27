---
title: Configuration reference
order: 60
---

# Configuration reference

`config/packages/wikidoc.yaml`, as `Base\Wikidoc\Bridge\Symfony\DependencyInjection\WikidocConfiguration`
declares it (`bin/console config:dump-reference wikidoc` in the core's harness, this
checkout mounted over `vendor/omnibase/docs`):

```yaml
wikidoc:

    # Documentation roots, lowest priority first; a later root overrides same-path pages.
    roots:

        # Prototype
        name:
            path:                 ~ # Required
            label:                null

    # The manuals: one per package, read in the docs/ folder and the README of its repository. Keyed by the package's name.
    manuals:

        # Prototype
        name:

            # The repository's checkout.
            path:                 ~ # Required
            label:                null
            description:          null

            # The Markdown folder inside the repository (default: wikidoc.docs).
            docs:                 null

            # The README inside the repository; false: none (default: wikidoc.readme).
            readme:               null

            # Its web address, for "Edit this page". Default: the checkout's origin remote.
            repository:           null
            default_version:      null

            # Versions with a checkout of their own: {"2.x": {path: ..., branch: "2.x"}}. Default: the branch checked out, and those wikidoc:sync exported.
            versions:

                # Prototype
                name:
                    path:                 ~ # Required
                    branch:               null

    # Glob patterns: every folder matched that holds a composer.json or a package.json, and a docs/ folder or a README, is a manual named after its package.
    discover:             []

    # Package names (or folders) left out of the discovery; fnmatch patterns.
    discover_exclude:     []

    # The Markdown folder inside a repository.
    docs:                 docs

    # The README inside a repository; false: none.
    readme:               README.md

    # The git remote a repository is published on.
    remote:               origin

    # The branches that are versions.
    branches:             /^(\d+\.(x|\d+)|main|master)$/

    # Where wikidoc:sync copies the branches that are not checked out.
    export_dir:           '%kernel.project_dir%/var/wikidoc'

    # "{repository}/edit/{branch}/{file}"; null: the forge's own convention.
    edit_url:             null

    # Seconds the discovered folders and the local search records are kept; 0: read again at each request.
    cache_ttl:            0
    public:

        # Registers the public controller (import @WikidocBundle/src/Site/Controller in the routes).
        enabled:              false

        # The address the manuals are published under.
        path:                 docs
    search:
        typesense:

            # Needs glitchr/typesense-bundle; without it, or with the server down, the local index answers.
            enabled:              false

            # The typesense-bundle connection; null: its default one.
            connection:           null

            # The collections are <prefix>_<manual>_<version>.
            prefix:               wikidoc

```

| Option | Type | Default | Effect | Page |
|---|---|---|---|---|
| `roots.<name>.path`, `.label` | string, required; string | ; the name, capitalised | The back office's roots, lowest priority first: a later root's page replaces an earlier one's of the same path. A root missing on disk is skipped | [The back office's manual](back-office.md) |
| `manuals.<package>` | map | | A manual by its package name: `path` (the checkout, required), `label`, `description`, `docs`, `readme`, `repository` (for "Edit this page"; default: the checkout's `origin`), `default_version`, `versions` (`{name: {path, branch}}` or `{name: path}`) | [Public manuals](manuals.md) |
| `discover` | list of globs | `[]` | Every folder matched with a `composer.json` or a `package.json` and a `docs/` folder or a README is a manual named after its package | [Public manuals](manuals.md#naming-them-or-finding-them) |
| `discover_exclude` | list of fnmatch patterns | `[]` | Packages or folders left out of the discovery | |
| `docs` | string | `docs` | The Markdown folder of a repository | |
| `readme` | string or `false` | `README.md` | The README of a repository; `false`: none | |
| `remote` | string | `origin` | The git remote a repository is published on | |
| `branches` | regular expression | `/^(\d+\.(x\|\d+)\|main\|master)$/` | The branches that are versions | [Versions](manuals.md#versions) |
| `export_dir` | string | `%kernel.project_dir%/var/wikidoc` | Where `wikidoc:sync` copies the branches that are not checked out | |
| `edit_url` | string | `null` | `{repository}/edit/{branch}/{file}`; `null`: the forge's own convention | |
| `cache_ttl` | int, 0 or more | `0` | Seconds the discovered folders and the local search records are kept; `0`: read again at each request | |
| `public.enabled` | bool | `false` | Registers the public controller (import `@WikidocBundle/src/Site/Controller` in the routes); off, it is removed from the container | [Public manuals](manuals.md#switching-them-on) |
| `public.path` | string | `docs` | The address the manuals are published under: a path without slashes around it (`docs`, `manuals/en`), else the configuration is refused | |
| `search.typesense.enabled` | bool | `false` | The search in Typesense; registered only when glitchr/typesense-bundle is installed too | [Search](search.md#typesense) |
| `search.typesense.connection` | string | `null` | The typesense-bundle connection; `null`: its default | |
| `search.typesense.prefix` | string | `wikidoc` | The collections are `<prefix>_<manual>_<version>` | |

The roots are given to `DocumentationRegistry` and the manuals' options to
`ManualRegistry` as arguments (their paths resolved: `%kernel.project_dir%`, an
`%env()%`); the other options are also parameters (`wikidoc.public.path`, which the
public routes read, `wikidoc.cache_ttl`...).
