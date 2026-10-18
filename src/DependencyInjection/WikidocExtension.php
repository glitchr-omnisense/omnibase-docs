<?php

namespace Base\Wikidoc\DependencyInjection;

// The name sites and tests knew: since step C8 of the plan "omnibase agnostique" the class is
// Base\Wikidoc\Bridge\Symfony\DependencyInjection\WikidocExtension, with the bundle's other Symfony glue under src/Bridge/Symfony.
if (!class_exists(WikidocExtension::class, false)) {
    class_alias(\Base\Wikidoc\Bridge\Symfony\DependencyInjection\WikidocExtension::class, WikidocExtension::class);
}

if (false) {
    /** @deprecated since 2.0, use \Base\Wikidoc\Bridge\Symfony\DependencyInjection\WikidocExtension */
    class WikidocExtension extends \Base\Wikidoc\Bridge\Symfony\DependencyInjection\WikidocExtension
    {
    }
}
