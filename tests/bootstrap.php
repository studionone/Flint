<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/mocks/SingletonMock.php';

error_reporting( E_ALL );

class FakeController
{
    public function indexAction()
    {
        return 'index';
    }

    public function listAction()
    {
        return 'list';
    }
}

class FakeSingleton
{
    use Flint\Singleton;

    public function returnTrue()
    {
        return true;
    }
}

class FakeService
{
    public function __construct(FakeService2 $fooService)
    {
        $this->foo = $fooService;
    }

    public function hello()
    {
        return "world".$this->foo->foo();
    }
}

class FakeService2
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function foo()
    {
        return "bar".$this->name;
    }
}

class SharedService
{
    public $time;

    public function __construct()
    {
        $this->time = microtime();
    }

    public function getTime()
    {
        return $this->time;
    }
}

