<?php

declare(strict_types=1);

namespace TMV\Messenger\Factory\Transport;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactory as SFTransportFactory;
use Symfony\Component\Messenger\Transport\TransportInterface;
use TMV\Messenger\Exception\InvalidArgumentException;

final class TransportFactory
{
    /** @var string */
    private $transportNameOrDsn;

    public function __construct(string $transportNameOrDsn)
    {
        $this->transportNameOrDsn = $transportNameOrDsn;
    }

    public function __invoke(ContainerInterface $container): TransportInterface
    {
        /** @var array|array<string, mixed> $config */
        $config = $container->has('config') ? $container->get('config') : [];

        $transportConfig = $config['messenger']['transports'][$this->transportNameOrDsn] ?? null;

        $dsn = null;
        $options = [];
        $serializerName = $config['messenger']['default_serializer'] ?? null;

        if (\is_array($transportConfig)) {
            $dsn = $transportConfig['dsn'] ?? null;
            $options = $transportConfig['options'] ?? [];
            $serializerName = $transportConfig['serializer'] ?? $serializerName;
        } elseif (\is_string($transportConfig)) {
            $dsn = $transportConfig;
        }

        if (null === $dsn) {
            $dsn = $this->transportNameOrDsn;
        }

        $transportFactory = $container->get(SFTransportFactory::class);
        /** @var SerializerInterface $serializer */
        $serializer = $serializerName
            ? $container->get($serializerName)
            : new PhpSerializer();

        return $transportFactory->createTransport($dsn, $options, $serializer);
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return TransportInterface
     */
    public static function __callStatic(string $name, array $arguments): TransportInterface
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
