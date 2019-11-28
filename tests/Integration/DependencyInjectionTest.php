<?php

declare(strict_types=1);

namespace TMV\Messenger\Test\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Command\ConsumeMessagesCommand;
use Symfony\Component\Messenger\MessageBusInterface;
use TMV\Messenger\ConfigProvider;
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
        $configProvider = new ConfigProvider();
        $config = $configProvider();
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
}
