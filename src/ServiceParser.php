<?php
namespace Flint;

use Flint\Exception\InvalidFileException,
    Flint\Exception\InvalidServicesFileException,
    Flint\Exception\InvalidServiceException;

/**
 * @method \Flint\ServiceParser getInstance()
 * @method array getServices()
 * @method string getServicesFile()
 * @method \Flint\ServiceParser setServices() NOTE: Only for debugging, be careful
 * @method \Flint\ServiceParser setServicesFile()
 */
class ServiceParser
{
    use Singleton;
    use Accessors;

    protected $servicesFile = '';
    protected $services = null;
    protected $controllers = null;

    public function __construct($servicesFile)
    {
        $this->setServicesFile($servicesFile);
    }

    public function loadServices()
    {
        try {
            $services = Config::getInstance()->load($this->getServicesFile());
        } catch (InvalidFileException $e) {
            throw new InvalidServicesFileException($e->getMessage());
        }

        $this->setServices($services);

        return $this;
    }

    public function loadControllers(array $controllers)
    {
        // Munge the controllers
        $fixed = [];
        foreach ($controllers as $name => $value) {
            // Make sure it's shared
            if (array_key_exists('share', $value) === false
             || $value['share'] !== true) {
                $value['share'] = true;
            }

            // Ensure it's name ends with 'controller'
            if (substr($name, strlen($name) - strlen('.controller')) !== '.controller') {
                throw new InvalidControllersFileException('ControllerService names must end in ".controller": ' . $name);
            }

            $fixed[$name] = $value;
        }

        $this->setControllers($fixed);

        return $this;
    }

    // TODO: Add in support for "shared" and "protected" callbacks
    public function parse()
    {
        $app = \Flint\App::getInstance();
        $services = $this->getServices();
        $controllers = $this->getControllers();

        if ($services === null) {
            throw new \ErrorException('Trying to parse loaded services before loading the file.');
        }

        // Append the controllers onto the service definitions
        if ($controllers === null) {
            $controllers = [];
        }

        $raw = array_merge($services, $controllers);

        foreach ($raw as $name => $values) {
            if (is_callable($values)) {
                $app[$name] = $values;
            } else if (isset($values['share']) && $values['share'] === true) {
                // Shared service; ie: saves a single copy of the object passed in
                $app[$name] = $app->share(function() use ($values) {
                    $class = new \ReflectionClass($values['class']);
                    $params = [];

                    if (isset($values['arguments']) && ! empty($values['arguments'])) {
                        foreach((array)$values['arguments'] as $argument) {
                            $params[] = $this->parseArgument($argument);
                        }

                        return $class->newInstanceArgs($params);
                    }

                    return $class->newInstance();
                });
            } else {
                // Regular Pimple/Silex service locator, runs constructor when you access
                $app[$name] = function() use ($values) {
                   $class = new \ReflectionClass($values['class']);
                   $params = [];

                   if (isset($values['arguments']) && ! empty($values['arguments'])) {
                       foreach((array)$values['arguments'] as $argument) {
                           $params[] = $this->parseArgument($argument);
                       }

                       return $class->newInstanceArgs($params);
                   }

                   return $class->newInstance();
               };
            }
        }

        return $app;
    }

    private function parseArgument($value)
    {
        if (is_string($value)) {
            if (strpos($value, '@') === 0) {
                $app = \Flint\App::getInstance();
                $value = $app[substr($value, 1)];
            }
        }

        return $value;
    }
}
