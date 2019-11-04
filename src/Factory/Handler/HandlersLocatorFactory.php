<?php

declare(strict_types=1);

namespace TMV\Messenger\Factory\Handler;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;

final class HandlersLocatorFactory
{
    /** @var string */
    private $busName;

    public function __construct(string $busName = 'messenger.bus.default')
    {
        $this->busName = $busName;
    }

    public function __invoke(ContainerInterface $container): HandlersLocatorInterface
    {
        /** @var array|array<string, mixed> $config */
        $config = $container->has('config') ? $container->get('config') : [];
        /** @var string[][]|array<string, string|callable> $senders */
        $handlerDescriptors = $config['messenger']['buses'][$this->busName]['handlers'] ?? [];

        /** @var array<string, array<HandlerDescriptor|callable>> $finalHandlers */
        $finalHandlers = [];

        foreach ($handlerDescriptors as $type => $handlers) {
            $finalHandlers[$type] = \array_map($this->getHandler($container), $handlers);
        }

        return new HandlersLocator($finalHandlers);
    }

    private function getHandler(ContainerInterface $container): callable
    {
        return static function ($handlerDescriptor) use ($container) {
            if (\is_string($handlerDescriptor)) {
                return $container->get($handlerDescriptor);
            }

            return $handlerDescriptor;
        };
    }
}
