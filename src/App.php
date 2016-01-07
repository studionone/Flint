<?php
namespace Flint;

use Silex\Application,
    Silex\Provider\ServiceControllerServiceProvider,
    Silex\Provider\ValidatorServiceProvider,
    Flint\Config,
    Flint\Exception\AppNotInstantiatedException,
    Flint\Exception\InvalidFileException,
    Flint\Exception\InvalidControllersFileException,
    Flint\Exception\InvalidControllerException;

/**
 * @method array getAppConfig
 * @method array getControllers
 * @method \Flint\App setAppConfig
 * @method \Flint\App getInstance
 */
class App extends \Silex\Application
{
    use Singleton;
    use Accessors; // Not convinced we need it yet

    protected $appConfig;
    protected $controllers;

    public function __construct(
        array $appConfig = [],
        array $silexConfig = []
    ) {
        $this->setAppConfig($appConfig);

        if (empty($this->getAppConfig())) {
            $this->setAppConfig($this->loadConfig(__DIR__ . '/../app/config.php'));
        }

        $silexConfig['debug'] = $this->appConfig['options']['debug'];

        parent::__construct($silexConfig);
    }

    public function loadConfig($configFile)
    {
        $config = Config::getInstance()->load($configFile);

        return $config;
    }


    public function loadControllers($controllerFile = '')
    {
        $this->register(new ServiceControllerServiceProvider());

        if ($controllerFile === '') {
            $controllerFile = $this->getAppConfig()['core']['configDir'] . $this->getAppConfig()['core']['controllersFile'];
        }

        try {
            $this->setControllers(Config::getInstance()->load($controllerFile));
        } catch (InvalidFileException $e) {
            throw new InvalidControllersFileException($e->getMessage());
        }

        return $this;
    }

    public function configureRoutes()
    {
        $routesFile = $this->getAppConfig()['core']['configDir'] . $this->getAppConfig()['core']['routesFile'];

        $routeParser = new RouteParser($routesFile);
        $routeParser->parse();

        return $this;
    }

    public function configureServices()
    {
        $servicesFile = $this->getAppConfig()['core']['configDir'] . $this->getAppConfig()['core']['servicesFile'];

        $serviceParser = new ServiceParser($servicesFile);
        $serviceParser->loadServices()->loadControllers($this->getControllers())->parse();

        // Sets up the Validator service provider
        $this->register(new ValidatorServiceProvider());

        return $this;
    }

    /**
     * For initialising Flint without relying on App::run()
     * Also ensures `@config` is injectable via service locator
     */
    public function setupServicesAndConfig()
    {
        $this['config'] = $this->getAppConfig();
        $this->loadControllers()
            ->configureServices()
            ->configureRoutes();
    }

    public function run(\Symfony\Component\HttpFoundation\Request $request = NULL)
    {
        // Initialisation of Flint
        $this->setupServicesAndConfig();

        // Initialisation of Silex
        $serviceOverride = null;
        if (method_exists($this, 'init')) {
            $serviceOverride = $this->init();
        }

        /**
         * Allows you to override the parent::run() method with a service inside the locator,
         * usually used for http_cache and other plugins
         */
        if ($serviceOverride !== null) {
            return $serviceOverride->run($request);
        }

        return parent::run($request);
    }
}
