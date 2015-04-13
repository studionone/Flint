<?php
namespace Flint\Tests;

require_once __DIR__ . "/../mocks/FakeApp.php";

use Flint\Tests\Mocks\FakeApp,
    Flint\Tests\Mocks\SingletonMock;

/**
 * @runTestsInSeparateProcesses
 */
class FakeAppTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->config = [
            'options' => [ 'debug' => 'true' ],
            'core' => [
                'configDir' => __DIR__ . '/../data',
                'controllersFile' => '/controllers.php',
                'routesFile' => '/routes.php',
                'servicesFile' => '/services.php'
            ]
        ];
    }

    public function testInitMethodCalledIfDefined()
    {
        FakeApp::destroyInstance();

        $stub =
            $this->getMockBuilder('Flint\Tests\Mocks\FakeApp')
            ->setMethods(['init'])
            ->setConstructorArgs([$this->config])
            ->getMock()
        ;

        $stub
            ->expects($this->once())
            ->method('init')
            ->will($this->returnValue($stub))
        ;

        SingletonMock::inject($stub, 'Flint\Tests\Mocks\FakeApp');

        $this->assertEquals($stub, FakeApp::getInstance());
        $stub->run();

        SingletonMock::cleanUp('Flint\Tests\Mocks\FakeApp');
    }
}
