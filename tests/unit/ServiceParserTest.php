<?php
namespace Flint\Tests;

use Flint\ServiceParser,
    Flint\Config,
    Flint\Tests\Mocks\SingletonMock,
    Flint\App;

class ServiceParserTest extends \PHPUnit_Framework_TestCase
{
    private $fakeConfig1 = [ 'hello' => 'world' ];
    private $fakeConfig2 = [

    ];

    public function tearDown()
    {
        ServiceParser::destroyInstance();
    }

    public function testCorrectInitialisation()
    {
        $parser = ServiceParser::getInstance('fakefile.php');

        $this->assertTrue('fakefile.php' === $parser->getServicesFile());
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

        $parser = ServiceParser::getInstance('fakefile.php');
        $parser->loadServices();

        $this->assertArrayHasKey('hello', $parser->getServices());

        SingletonMock::cleanUp('Flint\Config');
    }

    /**
     * @expectedException \Flint\Exception\InvalidServicesFileException
     */
    public function testInvalidServiceFileThrowsException()
    {
        $parser = ServiceParser::getInstance('blah');
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

        $parser = ServiceParser::getInstance('fakefile.php');
        $parser->parse();
    }

    public function testSingleServiceLoadedIntoAppCorrectly()
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

        $parser = ServiceParser::getInstance();
        $parser->loadServices()
            ->setServices($serviceConfig);

        $result = $parser->parse();

        $this->assertArrayHasKey('Fake', $result);

        $app = \Flint\App::getInstance();

        var_dump($app['Fake']->hello());

        SingletonMock::cleanUp('Flint\ServiceParser');
    }
}
