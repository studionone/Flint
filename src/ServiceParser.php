<?php
namespace Flint;

use Flint\Exception\InvalidFileException,
    Flint\Exception\InvalidServicesFileException,
    Flint\Exception\InvalidServiceException;

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
}
