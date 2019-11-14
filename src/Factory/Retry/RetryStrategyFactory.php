<?php

declare(strict_types=1);

namespace TMV\Messenger\Factory\Retry;

use function array_key_exists;
use function is_string;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;

final class RetryStrategyFactory
{
    /** @var string */
    private $transportName;

    public function __construct(string $transportName)
    {
        $this->transportName = $transportName;
    }

    public function __invoke(ContainerInterface $container): RetryStrategyInterface
    {
        /** @var array $config */
        $config = $container->has('config') ? $container->get('config') : [];

        /** @var array|array<string, float|int>|string $retryConfig */
        $retryConfig = $config['messenger']['transports'][$this->transportName]['retry_strategy'] ?? [];

        if (is_string($retryConfig)) {
            return $container->get($retryConfig);
        }

        $maxRetries = 3;

        if (array_key_exists('max_retries', $retryConfig)) {
            $maxRetries = null !== $retryConfig['max_retries'] ? (int) $retryConfig['max_retries'] : null;
        }

        return new MultiplierRetryStrategy(
            $maxRetries,
            (int) ($retryConfig['delay'] ?? 1000),
            (float) ($retryConfig['multiplier'] ?? 2.),
            (int) ($retryConfig['max_delay'] ?? 0)
        );
    }
}
