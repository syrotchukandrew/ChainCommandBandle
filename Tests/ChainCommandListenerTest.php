<?php

namespace ChainCommandBundle\Tests;

use ChainCommandBundle\EventListener\ChainCommandListener;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Output\BufferedOutput;
use ChainCommandBundle\Chain\CommandChain;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class ChainCommandListenerTest
 * @package ChainCommandBundle\Tests
 */
class ChainCommandListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandChain
     */
    private $commandChain;

    /**
     * @var ChainCommandListener
     */
    private $chainCommandListener;

    /**
     * @var Logger
     */
    private $logger;

    public function setUp()
    {
        $this->commandChain = new CommandChain();
        $this->logger = new Logger('log_monolog');
        $this->chainCommandListener = new ChainCommandListener($this->commandChain, $this->logger);
    }

    public function testOnConsoleCommand()
    {
        // Test for not main command in the chain, it shouldn't work on it own
        $commandFoo = $this->createMock(ContainerAwareCommand::class);
        $commandFoo->method('getName')->willReturn('foo:hello');
        $this->commandChain->addCommand($commandFoo, 'main', null);

        $commandBar = $this->createMock(ContainerAwareCommand::class);
        $commandBar->method('getName')->willReturn('bar:hi');
        $this->commandChain->addCommand($commandBar, 'foo:hello', 100);

        $output = new BufferedOutput();
        $event = $this->createMock(ConsoleCommandEvent::class);
        $event->method('getCommand')->willReturn($commandBar);
        $event->method('getOutput')->willReturn($output);
        $this->chainCommandListener->onConsoleCommand($event);

        $expected = sprintf(sprintf(
            "Error: %s command is a member of %s command chain and cannot be executed on its own.\n",
            $commandBar->getName(),
            $commandFoo->getName()
        ));
        $real = $output->fetch();
        $this->assertEquals($expected, $real);
    }
}