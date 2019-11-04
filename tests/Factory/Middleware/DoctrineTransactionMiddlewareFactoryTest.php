<?php

declare(strict_types=1);

namespace TMV\Messenger\Test\Factory\Middleware;

use Doctrine\Common\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use TMV\Messenger\Exception\InvalidArgumentException;
use TMV\Messenger\Factory\Middleware\DoctrineTransactionMiddlewareFactory;
use TMV\Messenger\Middleware\DoctrineTransactionMiddleware;

class DoctrineTransactionMiddlewareFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $managerRegistry = $this->prophesize(ManagerRegistry::class);

        $container->get(ManagerRegistry::class)
            ->shouldBeCalled()
            ->willReturn($managerRegistry->reveal());

        $factory = [DoctrineTransactionMiddlewareFactory::class, 'connection_name'];
        $service = $factory($container->reveal());

        $this->assertInstanceOf(DoctrineTransactionMiddleware::class, $service);
    }

    public function testInvalidCall(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $factory = [DoctrineTransactionMiddlewareFactory::class, 'connection_name'];
        $factory('foo');
    }
}
