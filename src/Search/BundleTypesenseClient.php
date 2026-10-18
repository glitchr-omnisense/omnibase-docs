<?php

namespace Base\Wikidoc\Search;

// The name sites and tests knew: since step C8 of the plan "omnibase agnostique" the class is
// Base\Wikidoc\Bridge\Symfony\Search\BundleTypesenseClient, with the bundle's other Symfony glue under src/Bridge/Symfony.
if (!class_exists(BundleTypesenseClient::class, false)) {
    class_alias(\Base\Wikidoc\Bridge\Symfony\Search\BundleTypesenseClient::class, BundleTypesenseClient::class);
}

if (false) {
    /** @deprecated since 2.0, use \Base\Wikidoc\Bridge\Symfony\Search\BundleTypesenseClient */
    class BundleTypesenseClient extends \Base\Wikidoc\Bridge\Symfony\Search\BundleTypesenseClient
    {
    }
}
