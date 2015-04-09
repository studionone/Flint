<?php
namespace Flint\Tests;

use Symfony\Component\HttpFoundation\Request,
    Flint\RouteParser,
    Flint\App;

class RouteParserTest extends \PHPUnit_Framework_TestCase
{
    protected $tmp;

    public function setUp()
    {
        $config = [
            'options' => [ 'debug' => true ],
            'core' => [
                'configDir' => __DIR__ . '/../data',
                'controllersFile' => '/controllers.php',
                'routesFile' => '/routes.php'
            ]
        ];

        $this->app = App::getInstance($config);
    }

    public function tearDown()
    {
        App::destroyInstance();
    }

    /**
     * @expectedException \Flint\Exception\InvalidRoutesFileException
     */
    public function testExceptionOnIncorrectRoutesFile()
    {
        $parser = new RouteParser('/hello/world/bleh.php');
        $parser->loadRoutes();
    }

    public function testLoadingRoutesFile()
    {
        $file = __DIR__ . '/../data/routes.php';
        $parser = new RouteParser($file);
        $data = $parser->loadRoutes()->getRoutes();

        $this->assertEquals($file, $parser->getRoutesFile());

        $this->assertTrue(is_array($data));
        $this->assertArrayHasKey('/', $data);
        $this->assertArrayHasKey('/fake', $data);
    }

    public function testRouteParsing()
    {
        $file = __DIR__ . '/../data/routes.php';
        $parser = new RouteParser($file);

        $this->app->loadControllers()
            ->configureControllers();

        $parser->loadRoutes()->parse();

        $fakeReq = Request::create('/');
        $r1 = $this->app->handle($fakeReq);
        $this->assertEquals('hello', $r1->getContent());

        $fakeReq2 = Request::create('/fake/');
        $r2 = $this->app->handle($fakeReq2);
        $this->assertEquals('index', $r2->getContent());

        $fakeReq3 = Request::create('/post');
        $fakeReq3->setMethod('POST');
        $r3 = $this->app->handle($fakeReq3);
        $this->assertEquals('from post', $r3->getContent());

        $fakeReq4 = Request::create('/put');
        $fakeReq4->setMethod('PUT');
        $r4 = $this->app->handle($fakeReq4);
        $this->assertEquals('from put', $r4->getContent());

        $fakeReq5 = Request::create('/delete');
        $fakeReq5->setMethod('DELETE');
        $r5 = $this->app->handle($fakeReq5);
        $this->assertEquals('from delete', $r5->getContent());


        $fakeReq6 = Request::create('/fake/list');
        $r6 = $this->app->handle($fakeReq6);
        $this->assertEquals('list', $r6->getContent());
    }
}
