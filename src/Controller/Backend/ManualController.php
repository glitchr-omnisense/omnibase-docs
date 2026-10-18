<?php

namespace Base\Wikidoc\Controller\Backend;

/**
 * The back office's manual where the sites import it: they read the routes of
 * the folder @WikidocBundle/src/Controller (config/routes.yaml), and get them
 * from this class, which inherits them. The controller itself is
 * Base\Wikidoc\Bridge\Symfony\Controller\Backend\ManualController since step
 * C8 of the plan "omnibase agnostique"; both are services (config/services.php).
 */
class ManualController extends \Base\Wikidoc\Bridge\Symfony\Controller\Backend\ManualController
{
}
