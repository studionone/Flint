<?php
namespace Flint\Tests;

use Flint\ServiceParser,
    Flint\Config,
    Flint\Tests\Mocks\SingletonMock,
    Flint\App;

class ServiceParserTest extends \PHPUnit_Framework_TestCase
{
    private $fakeConfig1 = [ 'hello' => 'world' ];
    private $fakeConfig2 = [];

    public function setUp()
    {
        ServiceParser::destroyInstance();
    }

    public function tearDown()
    {
        ServiceParser::destroyInstance();
        App::destroyInstance();
    }

    public function testCorrectInitialisation()
    {
        $parser = new ServiceParser('fakefile.php');

        $this->assertEquals('fakefile.php', $parser->getServicesFile());
    }

    public function testLoadServicesFileIntoParser()
    {
        /**
         * Mock out the Config loader
         */
        $confStub = $this->getMockBuilder('Flint\Config')->getMock();
        $confStub->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->fakeConfig1));

        SingletonMock::inject($confStub, 'Flint\Config');

        $parser = new ServiceParser('fakefile.php');
        $parser->loadServices();

        $this->assertArrayHasKey('hello', $parser->getServices());

        SingletonMock::cleanUp('Flint\Config');
    }

    /**
     * @expectedException \Flint\Exception\InvalidServicesFileException
     */
    public function testInvalidServiceFileThrowsException()
    {
        $parser = new ServiceParser('blah');
        $parser->loadServices();
    }

    /**
     * @expectedException ErrorException
     */
    public function testEmptyServiceParseCallThrowsException()
    {
        \Flint\App::getInstance([
            'options' => [
                'debug' => true
            ],
            'core' => [
                'configDir' => __DIR__ . '/../data'
            ]
        ]);

        $parser = new ServiceParser('fakefile.php');
        $parser->parse();
    }

    public function testCallableServiceLoadedIntoAppCorrectly()
    {
        \Flint\App::getInstance([
            'options' => [
                'debug' => true
            ],
            'core' => [
                'configDir' => __DIR__ . '/../data'
            ]
        ]);

        $serviceConfig = [
            'Hello' => function () {
                $item = new \stdClass;
                $item->hello = function () {
                    return 'testing';
                };

                return $item;
            }
        ];

        // Stub out the loadServices method so we isolate the parsing
        $stub = $this->getMockBuilder('Flint\ServiceParser')
            ->setConstructorArgs(['fakefile.php'])
            ->setMethods(['loadServices'])
            ->getMock();
        $stub->expects($this->any())
            ->method('loadServices')
            ->will($this->returnValue($stub));

        SingletonMock::inject($stub, 'Flint\ServiceParser');

        $stub->loadServices()
            ->setServices($serviceConfig);

        $result = $stub->parse();
        $this->assertArrayHasKey('Hello', $result);
        $hello = $result['Hello']->hello();
        $this->assertEquals($hello, 'testing');

        SingletonMock::cleanUp('Flint\ServiceParser');
    }

    public function testServicesLoadedIntoAppCorrectly()
    {
        \Flint\App::getInstance([
            'options' => [
                'debug' => true
            ],
            'core' => [
                'configDir' => __DIR__ . '/../data'
            ]
        ]);

        $serviceConfig = [
            'Fake' => [
                'class' => 'FakeService',
                'arguments' => [ '@Fake2' ]
            ],
            'Fake2' => [
                'class' => 'FakeService2',
                'arguments' => [ 'Josh' ],
            ],
            'Fake3' => [
                'class' => 'SharedService',
                'share' => true
            ],
            'Fake4' => [
                'class' => 'SharedService'
            ]
        ];

        // Stub out the loadServices method so we isolate the parsing
        $stub = $this->getMockBuilder('Flint\ServiceParser')
            ->setConstructorArgs(['fakefile.php'])
            ->setMethods(['loadServices'])
            ->getMock();
        $stub->expects($this->any())
            ->method('loadServices')
            ->will($this->returnValue($stub));

        SingletonMock::inject($stub, 'Flint\ServiceParser');

        $stub->loadServices()
            ->setServices($serviceConfig);

        $result = $stub->parse();

        $this->assertArrayHasKey('Fake', $result);
        $this->assertArrayHasKey('Fake2', $result);
        $this->assertArrayHasKey('Fake3', $result);

        $this->assertEquals('worldbarJosh', $result['Fake']->hello());

        // Testing whether the shared service worked correctly
        $time = $result['Fake3']->getTime();
        $this->assertEquals($time, \Flint\App::getInstance()['Fake3']->getTime());

        // Showing the invariant case for shared service, without the sharing
        $time = $result['Fake4']->getTime();
        $this->assertNotEquals($time, \Flint\App::getInstance()['Fake4']->getTime());

        SingletonMock::cleanUp('Flint\ServiceParser');
    }

    public function testSharedServiceWithArgLoadedCorrectly()
    {
        \Flint\App::getInstance([
            'options' => [
                'debug' => true
            ],
            'core' => [
                'configDir' => __DIR__ . '/../data'
            ]
        ]);

        $serviceConfig = [
            'Fake' => [
                'class' => 'SharedServiceWithArgs',
                'arguments' => [ 'Josh' ],
                'share' => true
            ]
        ];

        // Stub out the loadServices method so we isolate the parsing
        $stub = $this->getMockBuilder('Flint\ServiceParser')
            ->setConstructorArgs(['fakefile.php'])
            ->setMethods(['loadServices'])
            ->getMock();
        $stub->expects($this->any())
            ->method('loadServices')
            ->will($this->returnValue($stub));

        SingletonMock::inject($stub, 'Flint\ServiceParser');

        $stub->loadServices()
            ->setServices($serviceConfig);

        $result = $stub->parse();
        $this->assertArrayHasKey('Fake', $result);
        $time = $result['Fake']->getTime();
        $this->assertEquals('Josh', $result['Fake']->getName());
        $this->assertEquals($time, $result['Fake']->getTime());

        SingletonMock::cleanUp('Flint\ServiceParser');
    }
}
