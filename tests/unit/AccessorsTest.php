<?php
namespace Flint\Tests;

use Flint\Accessors;

class AccessorsStub
{
    use \Flint\Accessors;

    protected $created;
    private $testing;

    public function __construct()
    {
        $this->created = true;
    }
}


class AccessorsTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRealProperty()
    {
        $stub = new AccessorsStub();
        $this->assertTrue($stub->getCreated());
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage 'getFakeProperty' does not exist in 'Flint\Tests\AccessorsStub'.
     */
    public function testInvalidPropertyThrowsException()
    {
        $stub = new AccessorsStub();
        $stub->getFakeProperty();
    }

    public function testSetPrivateProperty()
    {
        $stub = new AccessorsStub();
        $this->assertNull($stub->getTesting());
        $stub->setTesting('hello');
        $this->assertEquals('hello', $stub->getTesting());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage 'setTesting' requires an argument value.
     */
    public function testSetWithoutParamThrowsException()
    {
        $stub = new AccessorsStub();
        $stub->setTesting();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage 'setNothingHere' does not exist in 'Flint\Tests\AccessorsStub'.
     */
    public function testSetInvalidPropertyThrowsException()
    {
        $stub = new AccessorsStub();
        $stub->setNothingHere('testing');
    }
}
