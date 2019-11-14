<?php

declare(strict_types=1);

namespace TMV\Messenger\Factory\Command;

use function array_keys;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\RoutableMessageBus;

final class ConsumeMessagesCommandFactory
{
    public function __invoke(ContainerInterface $container): ConsumeMessagesCommand
    {
        $config = $container->has('config') ? $container->get('config') : [];
        /** @var RoutableMessageBus $messageBus */
        $messageBus = $container->get('messenger.routable_message_bus');
        $logger = $config['messenger']['logger'] ?? null;
        $eventDispatcher = $config['messenger']['event_dispatcher'] ?? null;
        $transports = $config['messenger']['transports'] ?? [];
        /** @var ContainerInterface $retryStrategyLocator */
        $retryStrategyLocator = $container->get('messenger.retry_strategy_locator');
        /** @var string|null $cachePoolForRestartSignal */
        $cachePoolForRestartSignal = $config['messenger']['cache_pool_for_restart_signal'] ?? null;

        $command = new ConsumeMessagesCommand(
            $messageBus,
            $container->get('messenger.receivers_locator'),
            $logger ? $container->get($logger) : null,
            array_keys($transports),
            $retryStrategyLocator,
            $eventDispatcher ? $container->get($eventDispatcher) : null
        );

        if (null !== $cachePoolForRestartSignal) {
            $command->setCachePoolForRestartSignal($container->get($cachePoolForRestartSignal));
        }

        return $command;
    }
}
