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
