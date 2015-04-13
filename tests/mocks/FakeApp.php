<?php
namespace Flint\Tests\Mocks;

class FakeApp extends \Flint\App
{
    public function init()
    {
        return "hello";
    }
}
