<?php

declare(strict_types=1);

namespace TMV\Messenger\Test\Factory\Command;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Command\FailedMessagesRetryCommand;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Component\Messenger\RoutableMessageBus;
use Symfony\Component\Messenger\Transport\TransportInterface;
use TMV\Messenger\Exception\InvalidArgumentException;
use TMV\Messenger\Factory\Command\FailedMessagesRetryCommandFactory;

class FailedMessagesRetryCommandFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'messenger' => [
                'failure_transport' => 'failed',
                'event_dispatcher' => 'messenger.event_dispatcher',
                'logger' => 'messenger.logger',
                'transports' => [],
            ],
        ]);

        $receiversLocator = $this->prophesize(ContainerInterface::class);
        $failedTransport = $this->prophesize(TransportInterface::class);
        $routableMessageBus = $this->prophesize(RoutableMessageBus::class);
        $retryStrategyLocator = $this->prophesize(ContainerInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $retryStrategy = $this->prophesize(RetryStrategyInterface::class);

        $container->get('messenger.receivers_locator')
            ->shouldBeCalled()
            ->willReturn($receiversLocator->reveal());

        $container->get('messenger.routable_message_bus')
            ->shouldBeCalled()
            ->willReturn($routableMessageBus->reveal());

        $container->get('messenger.retry_strategy_locator')
            ->shouldBeCalled()
            ->willReturn($retryStrategyLocator->reveal());

        $container->get('messenger.logger')
            ->shouldBeCalled()
            ->willReturn($logger->reveal());

        $container->get('messenger.event_dispatcher')
            ->shouldBeCalled()
            ->willReturn($eventDispatcher->reveal());

        $receiversLocator->get('failed')
            ->shouldBeCalled()
            ->willReturn($failedTransport->reveal());

        $retryStrategyLocator->has('failed')->willReturn(true);
        $retryStrategyLocator->get('failed')
            ->shouldBeCalled()
            ->willReturn($retryStrategy->reveal());

        $factory = new FailedMessagesRetryCommandFactory();

        $service = $factory($container->reveal());

        $this->assertInstanceOf(FailedMessagesRetryCommand::class, $service);
    }

    public function testFactoryWithoutOptionalDependencies(): void
    {
        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'messenger' => [
                'failure_transport' => 'failed',
                'event_dispatcher' => 'messenger.event_dispatcher',
                'logger' => null,
                'transports' => [],
            ],
        ]);

        $receiversLocator = $this->prophesize(ContainerInterface::class);
        $failedTransport = $this->prophesize(TransportInterface::class);
        $routableMessageBus = $this->prophesize(RoutableMessageBus::class);
        $retryStrategyLocator = $this->prophesize(ContainerInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $retryStrategy = $this->prophesize(RetryStrategyInterface::class);

        $container->get('messenger.receivers_locator')
            ->shouldBeCalled()
            ->willReturn($receiversLocator->reveal());

        $container->get('messenger.routable_message_bus')
            ->shouldBeCalled()
            ->willReturn($routableMessageBus->reveal());

        $container->get('messenger.retry_strategy_locator')
            ->shouldBeCalled()
            ->willReturn($retryStrategyLocator->reveal());

        $container->get('messenger.logger')
            ->shouldNotBeCalled();

        $container->get('messenger.event_dispatcher')
            ->shouldBeCalled()
            ->willReturn($eventDispatcher->reveal());

        $receiversLocator->get('failed')
            ->shouldBeCalled()
            ->willReturn($failedTransport->reveal());

        $retryStrategyLocator->has('failed')->willReturn(false);
        $retryStrategyLocator->get('failed')
            ->shouldNotBeCalled();

        $factory = new FailedMessagesRetryCommandFactory();

        $service = $factory($container->reveal());

        $this->assertInstanceOf(FailedMessagesRetryCommand::class, $service);
    }

    public function testFactoryWithNoFailureTransportShouldThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid failure_transport name');

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'messenger' => [
                'failure_transport' => null,
            ],
        ]);

        $factory = new FailedMessagesRetryCommandFactory();

        $factory($container->reveal());
    }

    public function testFactoryWithNoEventDispatcherTransportShouldThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid event_dispatcher service');

        $container = $this->prophesize(ContainerInterface::class);

        $container->has('config')->willReturn(true);
        $container->get('config')->willReturn([
            'messenger' => [
                'failure_transport' => 'failed',
                'event_dispatcher' => null,
            ],
        ]);

        $factory = new FailedMessagesRetryCommandFactory();

        $factory($container->reveal());
    }
}
