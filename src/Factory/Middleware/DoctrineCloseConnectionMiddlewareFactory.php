<?php

declare(strict_types=1);

namespace TMV\Messenger\Factory\Middleware;

use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use TMV\Messenger\Middleware\DoctrineCloseConnectionMiddleware;

final class DoctrineCloseConnectionMiddlewareFactory extends AbstractDoctrineMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): MiddlewareInterface
    {
        return new DoctrineCloseConnectionMiddleware(
            $container->get(ManagerRegistry::class),
            $this->connectionName
        );
    }
}
