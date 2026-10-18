<?php

namespace Base\Wikidoc\Bridge\Symfony;

use Base\Bundle\AbstractBaseBundle;
use Base\Traits\SingletonTrait;

/**
 * omnibase/docs under Symfony: the container's services (config/services.php),
 * the `wikidoc:` configuration, the controllers, the commands, the templates
 * as @Wikidoc. The documentation itself - the roots, the manuals, their
 * Markdown, the search - is the bundle's root namespace and loads without the
 * framework (tests/Fondation.php, plan "omnibase agnostique", step C8).
 *
 * Sites register it as Base\Wikidoc\WikidocBundle, the subclass that keeps
 * that name.
 */
class WikidocBundle extends AbstractBaseBundle
{
    // Gives this bundle its own singleton storage instead of sharing
    // AbstractBaseBundle's - see that class's constructor for why this is
    // required on every concrete bundle extending it, not just this one.
    use SingletonTrait;

    // The trait's own protected no-op __construct() (there to force
    // singleton access through getInstance()) takes priority over the
    // INHERITED AbstractBaseBundle::__construct() the moment the trait is
    // used directly here, which both hides its real registration logic and
    // makes Symfony's `new WikidocBundle()` in bundles.php fatal (protected
    // constructor). Re-declaring it explicitly, public, delegating to
    // parent, restores both.
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Modern bundle layout: the bundle root is the package root - so
     * TwigBundle picks up ./templates as @Wikidoc (see the identical fix on
     * Base\Admin\AdminBundle), and @WikidocBundle/src/Controller is the
     * folder the sites import, wherever the class that registers it lives.
     */
    public function getPath(): string
    {
        return \dirname(__DIR__, 3);
    }

    /** Beside this class, whichever name registered the bundle (Symfony looks beside the registered class). */
    protected function getContainerExtensionClass(): string
    {
        return DependencyInjection\WikidocExtension::class;
    }
}
