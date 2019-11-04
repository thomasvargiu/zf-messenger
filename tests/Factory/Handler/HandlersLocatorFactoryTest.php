<?php

declare(strict_types=1);

namespace TMV\Messenger\Test\Factory\Handler;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use TMV\Messenger\Factory\Handler\HandlersLocatorFactory;

class HandlersLocatorFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $factory = new HandlersLocatorFactory('bus_name');

        $container = $this->prophesize(ContainerInterface::class);
        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'messenger' => [
                'buses' => [
                    'bus_name' => [
                        'handlers' => [
                            'foo' => [
                                'handler1',
                                function () {
                                },
                                new HandlerDescriptor(function () {
                                }),
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $handler = $this->prophesize(MessageHandlerInterface::class);

        $container->get('handler1')
            ->shouldBeCalled()
            ->willReturn($handler->reveal());

        /** @var HandlersLocator $service */
        $service = $factory($container->reveal());

        $this->assertInstanceOf(HandlersLocatorInterface::class, $service);
    }
}
