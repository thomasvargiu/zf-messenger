<?php

declare(strict_types=1);

namespace TMV\Messenger\Factory\Middleware;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Middleware\AddBusNameStampMiddleware;
use TMV\Messenger\Exception\InvalidArgumentException;

final class AddBusNameStampMiddlewareFactory
{
    /** @var string */
    private $busName;

    public function __construct(string $busName = 'messenger.bus.default')
    {
        $this->busName = $busName;
    }

    public function __invoke(ContainerInterface $container): AddBusNameStampMiddleware
    {
        return new AddBusNameStampMiddleware($this->busName);
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return AddBusNameStampMiddleware
     */
    public static function __callStatic(string $name, array $arguments): AddBusNameStampMiddleware
    {
        if (! \array_key_exists(0, $arguments) || ! $arguments[0] instanceof ContainerInterface) {
            throw new InvalidArgumentException(\sprintf(
                'The first argument must be of type %s',
                ContainerInterface::class
            ));
        }

        return (new static($name))($arguments[0]);
    }
}
