<?php

declare(strict_types=1);

namespace TMV\Messenger\Factory\Listener;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\EventListener\SendFailedMessageForRetryListener;

final class SendFailedMessageForRetryListenerFactory
{
    public function __invoke(ContainerInterface $container): SendFailedMessageForRetryListener
    {
        /** @var array $config */
        $config = $container->has('config') ? $container->get('config') : [];

        /** @var string|null $logger */
        $logger = $config['messenger']['logger'] ?? null;

        return new SendFailedMessageForRetryListener(
            $container,
            $container->get('messenger.retry_strategy_locator'),
            $logger ? $container->get($logger) : null
        );
    }
}
