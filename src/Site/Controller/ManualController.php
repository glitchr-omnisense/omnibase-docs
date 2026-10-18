<?php

namespace Base\Wikidoc\Site\Controller;

/**
 * The public manuals where the sites import them: they read the routes of the
 * folder @WikidocBundle/src/Site/Controller (config/routes.yaml), and get them
 * from this class, which inherits them. The controller itself is
 * Base\Wikidoc\Bridge\Symfony\Site\Controller\ManualController since step C8
 * of the plan "omnibase agnostique"; both are services (config/services.php),
 * both removed unless `wikidoc.public.enabled`.
 */
class ManualController extends \Base\Wikidoc\Bridge\Symfony\Site\Controller\ManualController
{
}
