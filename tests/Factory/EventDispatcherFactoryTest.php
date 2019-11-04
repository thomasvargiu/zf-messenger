<?php

declare(strict_types=1);

namespace TMV\Messenger\Test\Factory;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use TMV\Messenger\Factory\EventDispatcherFactory;

class EventDispatcherFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(Argument::cetera())->shouldNotBeCalled();

        $factory = new EventDispatcherFactory();

        $instance = $factory($container->reveal());

        $this->assertInstanceOf(EventDispatcherInterface::class, $instance);
    }

    public function testFactoryWithInstance(): void
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->shouldBeCalled()->willReturn([
            'messenger' => [
                'failure_transport' => 'failure-transport-name',
            ],
        ]);

        $listener = new class() implements EventSubscriberInterface {
            public static function getSubscribedEvents(): array
            {
                return ['foo' => ['bar', -100]];
            }
        };

        $container->get(SendFailedMessageToFailureTransportListener::class)
            ->shouldBeCalled()
            ->willReturn($listener);

        $factory = new EventDispatcherFactory();

        $instance = $factory($container->reveal());

        // this should instantiate the service
        $listeners = $instance->getListeners('foo');

        $this->assertCount(1, $listeners);
        $this->assertSame([$listener, 'bar'], $listeners[0]);
    }

    public function testFactoryWithNoFailureTransport(): void
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->shouldBeCalled()->willReturn([
            'messenger' => [
                'failure_transport' => null,
            ],
        ]);

        $container->get(SendFailedMessageToFailureTransportListener::class)
            ->shouldNotBeCalled();

        $factory = new EventDispatcherFactory();

        $instance = $factory($container->reveal());

        // this should instantiate the service
        $listeners = $instance->getListeners('foo');

        $this->assertCount(0, $listeners);
    }
}
