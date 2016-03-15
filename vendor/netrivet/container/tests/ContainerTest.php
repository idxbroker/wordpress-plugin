<?php

namespace NetRivet\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    protected $container;


    public function setUp()
    {
        $this->container = new Container();
    }


    public function testCanResolveDependencies()
    {
        $qux = $this->container->make('NetRivet\Container\Test\Qux');

        $baz = $qux->getBaz();

        $this->assertInstanceOf('NetRivet\Container\Test\Baz', $baz);
    }


    public function testCanBindInterfaceToImplementationUsingBind()
    {
        $this->container->bind('NetRivet\Container\Test\BarInterface', 'NetRivet\Container\Test\Bar1');
        $foo = $this->container->make('NetRivet\Container\Test\Foo');

        $bar = $foo->getBar();

        $this->assertInstanceOf('NetRivet\Container\Test\Bar1', $bar);
    }


    public function testCanBindInterfaceUsingWhenNeedsGive()
    {
        $this->container
            ->when('NetRivet\Container\Test\Foo')
            ->needs('NetRivet\Container\Test\BarInterface')
            ->give('NetRivet\Container\Test\Bar2');

        $foo = $this->container->make('NetRivet\Container\Test\Foo');
        $bar = $foo->getBar();

        $this->assertInstanceOf('NetRivet\Container\Test\Bar2', $bar);
    }
}
