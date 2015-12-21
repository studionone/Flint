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
                $name = null;
                $converter = null;

                // Grab the name if passed in
                if (array_key_exists(2, $def)
                 && $def[2] !== null) {
                    $name = $def[2];
                }

                // Grab the converter if passed in
                if (array_key_exists(3, $def)
                 && $def[3] !== null) {
                    $converter = $def[3];
                }

                $this->registerRoute($route, $def[0], $def[1], $name, $converter);
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
            $name = null;
            $converter = null;
            $assert = null;

            // Grab the name if passed in
            if (array_key_exists(2, $def)
             && $def[2] !== null) {
                $name = $def[2];
            }

            // Grab the converter if passed in
            if (array_key_exists(3, $def)
             && $def[3] !== null) {
                $converter = $def[3];
            }

            if (array_key_exists(4, $def)
             && $def[4] !== null) {
                $assert = $def[4];
            }

            switch (strtolower($def[0])) {
                case 'get':
                    $r = $group->get($route, $def[1]);
                    break;
                case 'post':
                    $r = $group->post($route, $def[1]);
                    break;
                case 'put':
                    $r = $group->put($route, $def[1]);
                    break;
                case 'delete':
                    $r = $group->delete($route, $def[1]);
                    break;
                default:
                    throw new InvalidRouteException('
                        Incorrect HTTP method for route `' . $base . $route . '`: ' . $def[0]
                    );
                    break;
            }

            if ($name !== null) {
                $r->bind($name);
            }

            if ($converter !== null) {
                $r->convert($converter[0], $converter[1]);
            }

            if ($assert !== null
             && is_array($assert)
             && count($assert) === 2) {
                $r->assert($assert[0], $assert[1]);
            }
        }

        $app->mount($base, $group);
    }

    private function registerRoute($route, $http, $method, $name = null, $converter = null, $assert = null)
    {
        $app = App::getInstance();

        switch (strtolower($http)) {
            case 'get':
                $r = $app->get($route, $method);
                break;
            case 'post':
                $r = $app->post($route, $method);
                break;
            case 'put':
                $r = $app->put($route, $method);
                break;
            case 'delete':
                $r = $app->delete($route, $method);
                break;
            default:
                throw new InvalidRouteException('
                    Incorrect HTTP method for route `' . $route . '`: ' . $http
                );
                break;
        }

        if ($name !== null) {
            $r->bind($name);
        }

        if ($converter !== null) {
            $r->convert($converter[0], $converter[1]);
        }

        if ($assert !== null
         && is_array($assert)
         && count($assert) === 2) {
            $r->assert($assert[0], $assert[1]);
        }
    }
}
