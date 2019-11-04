<?php

declare(strict_types=1);

namespace TMV\Messenger\Test\Factory\Listener;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\EventListener\SendFailedMessageToFailureTransportListener;
use Symfony\Component\Messenger\RoutableMessageBus;
use TMV\Messenger\Exception\InvalidArgumentException;
use TMV\Messenger\Factory\Listener\SendFailedMessageToFailureTransportListenerFactory;

class SendFailedMessageToFailureTransportListenerFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'messenger' => [
                'failure_transport' => 'failure-transport-name',
                'logger' => 'logger.service',
            ],
        ]);

        $messageBus = $this->prophesize(RoutableMessageBus::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $container->get('messenger.routable_message_bus')
            ->shouldBeCalled()
            ->willReturn($messageBus->reveal());
        $container->get('logger.service')
            ->shouldBeCalled()
            ->willReturn($logger->reveal());

        $factory = new SendFailedMessageToFailureTransportListenerFactory();
        $service = $factory($container->reveal());

        $this->assertInstanceOf(SendFailedMessageToFailureTransportListener::class, $service);
    }

    public function testFactoryShouldWorkWithoutLogger(): void
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'messenger' => [
                'failure_transport' => 'failure-transport-name',
                'logger' => null,
            ],
        ]);

        $messageBus = $this->prophesize(RoutableMessageBus::class);

        $container->get('messenger.routable_message_bus')
            ->shouldBeCalled()
            ->willReturn($messageBus->reveal());

        $factory = new SendFailedMessageToFailureTransportListenerFactory();
        $service = $factory($container->reveal());

        $this->assertInstanceOf(SendFailedMessageToFailureTransportListener::class, $service);
    }

    public function testFactoryShouldThrowExceptionWithoutFailureTransport(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'messenger' => [
                'failure_transport' => null,
            ],
        ]);

        $messageBus = $this->prophesize(RoutableMessageBus::class);

        $container->get('messenger.routable_message_bus')
            ->shouldBeCalled()
            ->willReturn($messageBus->reveal());

        $factory = new SendFailedMessageToFailureTransportListenerFactory();
        $service = $factory($container->reveal());
    }
}
