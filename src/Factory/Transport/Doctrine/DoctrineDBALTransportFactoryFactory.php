<?php

declare(strict_types=1);

namespace TMV\Messenger\Factory\Transport\Doctrine;

use Psr\Container\ContainerInterface;
use TMV\Messenger\Transport\Doctrine\DoctrineDBALTransportFactory;

class DoctrineDBALTransportFactoryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new DoctrineDBALTransportFactory($container);
    }
}
