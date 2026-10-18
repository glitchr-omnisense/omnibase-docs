<?php

// Run by omniscient/omnibase's docker/bin/bare before the classes (a script, sorted first): the
// classes loaded are this checkout's, whichever application's autoloader bare was given. Nothing
// else is loaded here.
foreach (Composer\Autoload\ClassLoader::getRegisteredLoaders() as $loader) {
    $loader->addPsr4('Base\\Wikidoc\\', dirname(__DIR__, 2).'/src', true);
}

return 'Base\\Wikidoc\\ in '.realpath(dirname(__DIR__, 2).'/src');
