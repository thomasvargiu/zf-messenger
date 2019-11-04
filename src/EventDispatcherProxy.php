<?php

declare(strict_types=1);

namespace TMV\Messenger;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EventDispatcherProxy implements EventDispatcherInterface
{
    /** @var callable */
    private $factory;

    /** @var false|EventDispatcherInterface */
    private $initialized = false;

    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    private function callInternalMethod(string $method, array $args = [])
    {
        return $this->initialized
            ? $this->initialized->{$method}(...$args)
            : ($this->initialized = ($this->factory)())->{$method}(...$args);
    }

    /**
     * @param string $eventName
     * @param callable $listener
     * @param int $priority
     */
    public function addListener($eventName, $listener, $priority = 0): void
    {
        $this->callInternalMethod(__FUNCTION__, \func_get_args());
    }

    /**
     * @param EventSubscriberInterface $subscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->callInternalMethod(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string $eventName
     * @param callable $listener
     */
    public function removeListener($eventName, $listener): void
    {
        $this->callInternalMethod(__FUNCTION__, \func_get_args());
    }

    /**
     * @param EventSubscriberInterface $subscriber
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->callInternalMethod(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|null $eventName
     *
     * @return array
     */
    public function getListeners($eventName = null): array
    {
        return $this->callInternalMethod(__FUNCTION__, \func_get_args());
    }

    /**
     * @param object $event
     *
     * @return object
     */
    public function dispatch($event)
    {
        return $this->callInternalMethod(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string $eventName
     * @param callable $listener
     *
     * @return int|null
     */
    public function getListenerPriority($eventName, $listener): ?int
    {
        return $this->callInternalMethod(__FUNCTION__, \func_get_args());
    }

    /**
     * @param string|null $eventName
     *
     * @return bool
     */
    public function hasListeners($eventName = null): bool
    {
        return $this->callInternalMethod(__FUNCTION__, \func_get_args());
    }
}
