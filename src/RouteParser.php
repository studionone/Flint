<?php
namespace Flint;

use Flint\Exception\InvalidRoutesFileException,
    Flint\Exception\InvalidFileException,
    Flint\Exception\InvalidRouteException;

/**
 * @method array getRoutes
 * @method \Flint\RouteParser setRoutes
 * @method string getRoutesFile
 * @method \Flint\RouteParser setRoutesFile
 */
class RouteParser
{
    use Accessors;

    protected $routes;
    protected $routesFile;

    public function __construct($routesFile)
    {
        $this->routesFile = $routesFile;
    }

    public function loadRoutes()
    {
        try {
            $routes = Config::getInstance()->load($this->routesFile);
        } catch (InvalidFileException $e) {
            throw new InvalidRoutesFileException($e->getMessage());
        }

        $this->routes = $routes;

        return $this;
    }

    public function parse()
    {
        $this->loadRoutes();

        foreach ($this->routes as $route => $def) {
            if (! $this->isGroup($def)) {
                // register route directly
                $this->registerRoute($route, $def[0], $def[1]);
                continue;
            } else {
                // is a group
                $this->registerGroup($route, $def);
            }
        }

        return $this;
    }

    public function isGroup(array $route)
    {
        if (array_key_exists(0, $route)) {
            return false;
        }

        return true;
    }

    private function registerGroup($base, $vals)
    {
        $app = App::getInstance();
        $group = $app['controllers_factory'];
        foreach ($vals as $route => $def) {
            switch (strtolower($def[0])) {
                case 'get':
                    $group->get($route, $def[1]);
                    break;
                case 'post':
                    $group->post($route, $def[1]);
                    break;
                case 'put':
                    $group->put($route, $def[1]);
                    break;
                case 'delete':
                    $group->delete($route, $def[1]);
                    break;
                default:
                    throw new InvalidRouteException('
                        Incorrect HTTP method for route `' . $base . $route . '`: ' . $def[0]
                    );
                    break;
            }
        }

        $app->mount($base, $group);
    }

    private function registerRoute($route, $http, $method)
    {
        $app = App::getInstance();

        switch (strtolower($http)) {
            case 'get':
                $app->get($route, $method);
                break;
            case 'post':
                $app->post($route, $method);
                break;
            case 'put':
                $app->put($route, $method);
                break;
            case 'delete':
                $app->delete($route, $method);
                break;
            default:
                throw new InvalidRouteException('
                    Incorrect HTTP method for route `' . $route . '`: ' . $http
                );
                break;
        }
    }
}
