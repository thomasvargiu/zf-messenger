<?php

declare(strict_types=1);

namespace TMV\Messenger\Test\Factory\Command;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\RoutableMessageBus;
use TMV\Messenger\Factory\Command\ConsumeMessagesCommandFactory;

class ConsumeMessagesCommandFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'messenger' => [
                'event_dispatcher' => 'my.event_dispatcher',
                'logger' => 'my.logger',
                'transports' => [],
            ],
        ]);

        $routableMessageBus = $this->prophesize(RoutableMessageBus::class);
        $receiversLocator = $this->prophesize(ContainerInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $container->get('messenger.routable_message_bus')
            ->shouldBeCalled()
            ->willReturn($routableMessageBus->reveal());

        $container->get('messenger.receivers_locator')
            ->shouldBeCalled()
            ->willReturn($receiversLocator->reveal());

        $container->get('my.event_dispatcher')
            ->shouldBeCalled()
            ->willReturn($eventDispatcher->reveal());

        $container->get('my.logger')
            ->shouldBeCalled()
            ->willReturn($logger->reveal());

        $factory = new ConsumeMessagesCommandFactory();

        $service = $factory($container->reveal());

        $this->assertInstanceOf(ConsumeMessagesCommand::class, $service);
    }

    public function testFactoryWithoutOptionalDependencies(): void
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'messenger' => [
                'event_dispatcher' => null,
                'logger' => null,
                'transports' => [],
            ],
        ]);

        $routableMessageBus = $this->prophesize(RoutableMessageBus::class);
        $receiversLocator = $this->prophesize(ContainerInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $container->get('messenger.routable_message_bus')
            ->shouldBeCalled()
            ->willReturn($routableMessageBus->reveal());

        $container->get('messenger.receivers_locator')
            ->shouldBeCalled()
            ->willReturn($receiversLocator->reveal());

        $container->get('messenger.event_dispatcher')
            ->shouldBeCalled()
            ->willReturn($eventDispatcher->reveal());

        $container->get('messenger.logger')
            ->shouldNotBeCalled()
            ->willReturn($logger->reveal());

        $factory = new ConsumeMessagesCommandFactory();

        $service = $factory($container->reveal());

        $this->assertInstanceOf(ConsumeMessagesCommand::class, $service);
    }
}
