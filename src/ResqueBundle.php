<?php

class ResqueBundle
{
    public static function register($app)
    {
        self::loadServices($app);
    }

    public static function loadServices($app)
    {
        $container = $app->getServiceContainer();

        // Initialise le parametres services comme un tableau vide s'il n'existe pas sinon fussion un autre tableau à celui déjà existant
        $services = $app->getConf()->loadConfigurationFile("services", __DIR__.DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."config");

        if(!$container->has("services")) $container->services = array();

        $container->services = array_merge($container->services, $services);

        $container["resque.vendor_dir"] = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..";

        $container["resque.logs_dir"] = APP_DIRECTORY.DIRECTORY_SEPARATOR."logs";

        $eventManager = $app->getEventManager();


    }
}
