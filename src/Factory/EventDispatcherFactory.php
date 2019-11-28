<?php

declare(strict_types=1);

namespace TMV\Messenger\Factory;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;
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

            $eventDispatcher->addSubscriber($container->get(SendFailedMessageForRetryListener::class));

            $failureTransport = $config['messenger']['failure_transport'] ?? null;

            if ($failureTransport) {
                $eventDispatcher->addSubscriber($container->get(SendFailedMessageToFailureTransportListener::class));
            }

            $cachePoolForRestartSignal = $config['messenger']['cache_pool_for_restart_signal'] ?? null;

            if ($cachePoolForRestartSignal) {
                $eventDispatcher->addSubscriber($container->get(StopWorkerOnRestartSignalListener::class));
            }

            return $eventDispatcher;
        };

        return new EventDispatcherProxy($factory);
    }
}
