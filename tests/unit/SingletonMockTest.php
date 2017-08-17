<?php
namespace Flint\Tests;

use Flint\Tests\Mocks\SingletonMock;

class SingletonMockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \ErrorException
     */
    public function testThrowsErrorExceptionIfClassIsntASingleton()
    {
        $stub = $this->getMockBuilder('FakeController')->getMock();
        SingletonMock::inject($stub, 'FakeController');
    }

    public function testMockInjectedSuccessfully()
    {
        $class = 'FakeSingleton';

        $stub = $this->getMockBuilder($class)->getMock();
        $stub->expects($this->any())
            ->method('returnTrue')
            ->will($this->returnValue(false));

        $original = \FakeSingleton::getInstance();

        SingletonMock::inject($stub, $class);

        $shouldBeStub = \FakeSingleton::getInstance();

        $this->assertInstanceOf('FakeSingleton', $original);
        $this->assertInstanceOf('PHPUnit_Framework_MockObject_MockObject', $shouldBeStub);

        $this->assertTrue($original->returnTrue());
        $this->assertFalse($shouldBeStub->returnTrue());
    }
}
