<?php

namespace Base\Wikidoc\DependencyInjection;

// The name sites and tests knew: since step C8 of the plan "omnibase agnostique" the class is
// Base\Wikidoc\Bridge\Symfony\DependencyInjection\WikidocConfiguration, with the bundle's other Symfony glue under src/Bridge/Symfony.
if (!class_exists(WikidocConfiguration::class, false)) {
    class_alias(\Base\Wikidoc\Bridge\Symfony\DependencyInjection\WikidocConfiguration::class, WikidocConfiguration::class);
}

if (false) {
    /** @deprecated since 2.0, use \Base\Wikidoc\Bridge\Symfony\DependencyInjection\WikidocConfiguration */
    class WikidocConfiguration extends \Base\Wikidoc\Bridge\Symfony\DependencyInjection\WikidocConfiguration
    {
    }
}
