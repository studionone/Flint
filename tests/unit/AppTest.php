<?php
namespace Flint\Tests;

use Silex\Application;
use Flint\App;
use Flint\ServiceParser;
use Flint\Tests\Mocks\SingletonMock;
use Symfony\Component\HttpFoundation\Request;

class AppTest extends \PHPUnit_Framework_TestCase
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

    public function tearDown()
    {
        App::destroyInstance();
    }

    public function testInitialisation()
    {
        $s = new \Silex\Application();
        $app = App::getInstance($this->config);

        $this->assertInstanceOf('\Silex\Application', $app);
        $this->assertInstanceOf('\Flint\App', $app);
        $this->assertArrayHasKey('core', $app->getAppConfig());
    }

    public function testConfigOverride()
    {
        $app = App::getInstance($this->config);

        $config = $app->getAppConfig();

        $this->assertArrayHasKey('debug', $config['options']);
        $this->assertArrayHasKey('routesFile', $config['core']);
        $this->assertArrayHasKey('configDir', $config['core']);
    }

    public function testServicesLoadedIntoPimple()
    {
        $app = App::getInstance($this->config);

        $app->loadControllers();
        $app->configureServices();

        $this->assertArrayHasKey('Fake', $app);
        $this->assertArrayHasKey('Fake2', $app);
    }

    public function testRoutesAreLoadedInCorrectly()
    {
        $app = App::getInstance($this->config);

        $app->loadControllers()
            ->configureServices()
            ->configureRoutes();

        // Now see if the routes are loaded
        $fakeReq = Request::create('/');
        $r1 = $app->handle($fakeReq);
        $this->assertEquals('hello', $r1->getContent());
    }
}
