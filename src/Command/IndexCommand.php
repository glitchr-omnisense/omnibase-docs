<?php

namespace Base\Wikidoc\Command;

// The name sites and tests knew: since step C8 of the plan "omnibase agnostique" the class is
// Base\Wikidoc\Bridge\Symfony\Command\IndexCommand, with the bundle's other Symfony glue under src/Bridge/Symfony.
if (!class_exists(IndexCommand::class, false)) {
    class_alias(\Base\Wikidoc\Bridge\Symfony\Command\IndexCommand::class, IndexCommand::class);
}

if (false) {
    /** @deprecated since 2.0, use \Base\Wikidoc\Bridge\Symfony\Command\IndexCommand */
    class IndexCommand extends \Base\Wikidoc\Bridge\Symfony\Command\IndexCommand
    {
    }
}
