<?php

/*
 * omnibase/docs without the framework: the classes of the bundle that load with
 * Composer's autoloader alone - no kernel, no container, no bundle booted
 * (plan "omnibase agnostique", step C8). tests/BareTest.php loads each of them
 * through omniscient/omnibase's docker/bin/bare, in a PHP process apart from
 * PHPUnit's, and fails if a class or a file of the framework is loaded on the
 * way (the Symfony bundles and bridges, dependency-injection, config,
 * http-kernel, http-foundation, routing, security-*, form, console, the
 * third-party bundles; not the standalone Symfony components - Finder,
 * Process -, Doctrine nor Twig).
 *
 * It is the whole root namespace: the documentation roots and their pages
 * (Documentation), the public manuals, their versions, their Markdown and the
 * export of a branch (Manual), the search and its index (Search). What needs
 * Symfony is under src/Bridge/Symfony; the classes left at their former names
 * for the sites (BareTest::KEPT) are the bridge's.
 *
 * The list only grows; a class leaves it only by a decision written down in
 * the plan. Sorted. 20 classes on 2026-10-18.
 */
return [
    'Base\Wikidoc\Documentation\DocPage',
    'Base\Wikidoc\Documentation\DocumentationRegistry',
    'Base\Wikidoc\Documentation\MarkdownRenderer',
    'Base\Wikidoc\Documentation\SearchIndexBuilder',
    'Base\Wikidoc\Manual\BranchExporter',
    'Base\Wikidoc\Manual\Git',
    'Base\Wikidoc\Manual\Manual',
    'Base\Wikidoc\Manual\ManualLocation',
    'Base\Wikidoc\Manual\ManualMarkdownRenderer',
    'Base\Wikidoc\Manual\ManualRegistry',
    'Base\Wikidoc\Manual\ManualVersion',
    'Base\Wikidoc\Manual\PageRenderer',
    'Base\Wikidoc\Manual\RenderedPage',
    'Base\Wikidoc\Search\LocalSearch',
    'Base\Wikidoc\Search\ManualIndex',
    'Base\Wikidoc\Search\ManualSearch',
    'Base\Wikidoc\Search\SearchHit',
    'Base\Wikidoc\Search\SearchResult',
    'Base\Wikidoc\Search\TypesenseClientInterface',
    'Base\Wikidoc\Search\TypesenseIndex',
];
