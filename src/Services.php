<?php
namespace Flint;

use Flint\Exception\InvalidServiceException;

class Services
{
    public static function get($service)
    {
        $app = App::getInstance();

        if (! array_key_exists($service, $app)) {
            throw new InvalidServiceException("Service `" . $service . "` does not exist");
        }

        return $app[$service];
    }

    public static function app()
    {
        return App::getInstance();
    }
}
