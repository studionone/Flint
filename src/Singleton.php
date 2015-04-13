<?php
namespace Flint;

trait Singleton
{
    protected static $instance;

    final public static function getInstance()
    {
        if ( ! isset(static::$instance)) {
            $class = new \ReflectionClass(__CLASS__);
            static::$instance = $class->newInstanceArgs(func_get_args());
        }

        return static::$instance;
    }

    final public static function destroyInstance()
    {
        self::$instance = NULL;
    }

    /**
     * @codeCoverageIgnore
     */
    final private function __clone() { }

    /**
     * @codeCoverageIgnore
     */
    final private function __wakeup() { }
}
