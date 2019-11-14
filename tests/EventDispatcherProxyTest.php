<?php

declare(strict_types=1);

namespace TMV\Messenger\Test;

use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TMV\Messenger\EventDispatcherProxy;

class EventDispatcherProxyTest extends TestCase
{
    private $eventDispatcher;

    private $proxy;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $factory = function () {
            return $this->eventDispatcher->reveal();
        };

        $this->proxy = new EventDispatcherProxy($factory);
    }

    public function testRemoveListener(): void
    {
        $listener = static function () {
        };
        $this->eventDispatcher->removeListener('foo', $listener)
            ->shouldBeCalled();

        $this->proxy->removeListener('foo', $listener);
    }

    public function testGetListenerPriority(): void
    {
        $listener = static function () {
        };
        $this->eventDispatcher->getListenerPriority('foo', $listener)
            ->shouldBeCalled()
            ->willReturn(5);

        $this->assertSame(5, $this->proxy->getListenerPriority('foo', $listener));
    }

    public function testAddSubscriber(): void
    {
        $subscriber = new class() implements EventSubscriberInterface {
            public static function getSubscribedEvents()
            {
                return ['foo' => ['bar', -100]];
            }
        };
        $this->eventDispatcher->addSubscriber($subscriber)
            ->shouldBeCalled();

        $this->proxy->addSubscriber($subscriber);
    }

    public function testRemoveSubscriber(): void
    {
        $subscriber = new class() implements EventSubscriberInterface {
            public static function getSubscribedEvents()
            {
                return ['foo' => ['bar', -100]];
            }
        };
        $this->eventDispatcher->removeSubscriber($subscriber)
            ->shouldBeCalled();

        $this->proxy->removeSubscriber($subscriber);
    }

    public function testGetListeners(): void
    {
        $listeners = ['foo' => 'bar'];
        $this->eventDispatcher->getListeners('foo')
            ->shouldBeCalled()
            ->willReturn($listeners);

        $this->assertSame($listeners, $this->proxy->getListeners('foo'));
    }

    public function testAddListener(): void
    {
        $listener = static function () {
        };
        $this->eventDispatcher->addListener('foo', $listener)
            ->shouldBeCalled();

        $this->proxy->addListener('foo', $listener);
    }

    public function testHasListeners(): void
    {
        $this->eventDispatcher->hasListeners('foo')
            ->shouldBeCalled()
            ->willReturn(true);
        $this->eventDispatcher->hasListeners('bar')
            ->shouldBeCalled()
            ->willReturn(false);

        $this->assertTrue($this->proxy->hasListeners('foo'));
        $this->assertFalse($this->proxy->hasListeners('bar'));
    }

    public function testDispatch(): void
    {
        $object = new stdClass();
        $this->eventDispatcher->dispatch($object)
            ->shouldBeCalled();

        $this->proxy->dispatch($object);
    }
}
