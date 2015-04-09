<?php
namespace Flint;

trait Singleton
{
    protected static $instance;

    final public static function getInstance()
    {
        if ( ! isset(self::$instance)) {
            $class = new \ReflectionClass(__CLASS__);
            self::$instance = $class->newInstanceArgs(func_get_args());
        }

        return self::$instance;
    }

    final public static function destroyInstance()
    {
        self::$instance = NULL;
    }

    final private function __clone() { }

    final private function __wakeup() { }
}
