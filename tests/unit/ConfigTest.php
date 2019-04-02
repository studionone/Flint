<?php
namespace Flint\Tests;

use Flint\Config,
    Flint\Tests\Mocks\SingletonMock,
    Silex\Application;


class ConfigTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        /**
         * Reset the stubbed out Config object
         */
        SingletonMock::cleanUp('Flint\Config');
    }

    public function testMocking()
    {
        /**
         * Creates our stub and sets up the return values we expect
         */
        $stub = $this->getMockBuilder('Flint\Config')->getMock();
        $stub->expects($this->any())
            ->method('load')
            ->will($this->returnValue('hello'));

        $orig = Config::getInstance();

        /**
         * Inject the stub into the singleton
         */
        SingletonMock::inject($stub, 'Flint\Config');

        $this->assertFalse(Config::getInstance() === $orig);
        $this->assertTrue('hello' === Config::getInstance()->load('test'));
    }

    /**
     * @expectedException Flint\Exception\InvalidFileException
     * @expectedExceptionMessage Config can't be loaded, file does not exist: fake
     */
    public function testLoadInvalidFileThrowsException()
    {
        $fakeFile = 'fake';
        Config::getInstance()->load($fakeFile);
    }
}
