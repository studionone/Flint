<?php
namespace Flint;

use Silex\Application,
    Silex\Provider\ServiceControllerServiceProvider,
    Symfony\Component\HttpKernel\Debug\ErrorHandler,
    Symfony\Component\HttpKernel\Debug\ExceptionHandler,
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
        // @codeCoverageIgnoreStart
        ErrorHandler::register();
        if (! 'cli' === php_sapi_name()) {
            ExceptionHandler::register();
        }
        // @codeCoverageIgnoreEnd

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

    public function configureControllers()
    {
        $this->register(new ServiceControllerServiceProvider());

        foreach ($this->getControllers() as $name => $callable) {
            if (! is_callable($callable)) {
                throw new InvalidControllerException('Controller `'.$name.'` is not a callable');
            }

            $app = $this;
            $app[$name . '.controller'] = $app->share($callable);
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

        $serviceParser = ServiceParser::getInstance($servicesFile);
        $serviceParser->loadServices()->parse();
    }

    /**
     * @codeCoverageIgnore
     */
    public function run(\Symfony\Component\HttpFoundation\Request $request = NULL)
    {
        $this->configureServices()
            ->loadControllers()
            ->configureControllers()
            ->configureRoutes();

        return parent::run($request);
    }
}
