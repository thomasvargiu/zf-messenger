<?php

declare(strict_types=1);

namespace TMV\Messenger\Factory;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use TMV\Messenger\EventDispatcherProxy;

final class EventDispatcherFactory
{
    public function __invoke(ContainerInterface $container): EventDispatcherInterface
    {
        // create a proxy to avoid circular dependencies

        $factory = static function () use ($container): EventDispatcher {
            /** @var array $config */
            $config = $container->has('config') ? $container->get('config') : [];

            $eventDispatcher = new EventDispatcher();

            $failureTransport = $config['messenger']['failure_transport'] ?? null;

            if ($failureTransport) {
                $listener = $container->get(SendFailedMessageToFailureTransportListener::class);
                $eventDispatcher->addSubscriber($listener);
            }

            return $eventDispatcher;
        };

        return new EventDispatcherProxy($factory);
    }
}
