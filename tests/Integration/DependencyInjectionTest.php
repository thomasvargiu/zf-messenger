<?php

declare(strict_types=1);

namespace TMV\Messenger\Test\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use TMV\Messenger\ConfigProvider;
use TMV\Messenger\Factory\MessageBusFactory;
use TMV\Messenger\Factory\Transport\TransportFactory;
use TMV\Messenger\Test\Integration\Stubs\Command\CommandStub;
use TMV\Messenger\Test\Integration\Stubs\Handler\TestHandler;
use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * @coversNothing
 */
class DependencyInjectionTest extends TestCase
{
    /** @var ContainerInterface */
    private $container;

    protected function setUp(): void
    {
        $aggregator = new ConfigAggregator([
            ConfigProvider::class,
            new ArrayProvider([
                'dependencies' => [
                    'factories' => [
                        TestHandler::class => InvokableFactory::class,
                        'messenger.bus.test' => [MessageBusFactory::class, 'messenger.bus.test'],
                        'messenger.transport.test' => [TransportFactory::class, 'messenger.transport.test'],
                    ],
                ],
                'messenger' => [
                    'buses' => [
                        'messenger.bus.test' => [
                            'handlers' => [
                                CommandStub::class => [TestHandler::class],
                            ],
                            'routes' => [
                                CommandStub::class => ['messenger.transport.test'],
                            ],
                        ],
                    ],
                    'transports' => [
                        'messenger.transport.test' => 'sync://',
                    ],
                ],
            ]),
        ]);
        $config = $aggregator->getMergedConfig();
        $this->container = new ServiceManager($config['dependencies']);
        $this->container->setService('config', $config);
    }

    public function testGetConsumeMessagesCommand(): void
    {
        $command = $this->container->get(ConsumeMessagesCommand::class);
        $this->assertInstanceOf(ConsumeMessagesCommand::class, $command);
    }

    public function testSetupBus(): void
    {
        $messageBus = $this->container->get('messenger.bus.default');
        $this->assertInstanceOf(MessageBusInterface::class, $messageBus);
    }

    public function testBusTest(): void
    {
        /** @var MessageBusInterface $messageBus */
        $messageBus = $this->container->get('messenger.bus.test');
        $this->assertInstanceOf(MessageBusInterface::class, $messageBus);

        /** @var TestHandler $testHandler */
        $testHandler = $this->container->get(TestHandler::class);

        $messageBus->dispatch(new CommandStub());

        $this->assertCount(1, $testHandler->getHandledMessages());
    }
}
