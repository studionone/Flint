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
    protected $services = [];

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

    public function parse()
    {
        $app = \Flint\App::getInstance();
        $raw = $this->getServices();

        if (empty($raw)) {
            throw new \ErrorException('Trying to parse loaded services before loading the file.');
        }

        foreach ($raw as $name => $values) {
            $app[$name] = function() use ($values) {
                $class = new \ReflectionClass($values['class']);
                $params = [];

                foreach((array)$values['arguments'] as $argument) {
                    $params[] = $this->parseArgument($argument);
                }

                return $class->newInstanceArgs($params);
            };
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
