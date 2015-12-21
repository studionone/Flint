<?php
namespace Flint\Tests\Mocks;

class SingletonMock
{
    /**
     * Injects a given PHPUnit stub object into a given class that utilises the Singleton trait.
     *
     * WARNING: Remember to call `cleanUp` either at the end of your test case, or in the
     * `tearDown` method of your test class
     *
     * @throws ErrorException when the class passed in doesn't use the Singleton trait
     * @param PHPUnit_Framework_MockObject_MockObject $stub
     * @param string $className
     * @return void
     */
    public static function inject(\PHPUnit_Framework_MockObject_MockObject $stub, $className)
    {
        if (is_subclass_of($className, 'Flint\App')
         || array_key_exists('Flint\Singleton', class_uses($className, true))) {
            // Replace the reference in the singleton so getInstance returns our mock
            $ref = new \ReflectionProperty($className, 'instance');
            $ref->setAccessible(true);
            $ref->setValue(null, $stub);
        } else {
            throw new \ErrorException("Trying to inject a stub into a class that doesn't use the Flint\Singleton trait: ".$className);
        }
    }

    /**
     * To be called when the stub is no longer needed
     *
     * @param string $className
     * @return void
     */
    public static function cleanUp($className)
    {
        $className::destroyInstance();
    }
}
