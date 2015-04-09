<?php
namespace Flint\Tests;

use Flint\ServiceParser,
    Flint\Config,
    Flint\Tests\Mocks\SingletonMock;

class ServiceParserTest extends \PHPUnit_Framework_TestCase
{
    private $fakeConfig1 = [ 'hello' => 'world' ];
    private $fakeConfig2 = [

    ];

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
}
