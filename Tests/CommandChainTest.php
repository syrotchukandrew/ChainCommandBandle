<?php

namespace ChainCommandBundle\Tests;

use ChainCommandBundle\Chain\CommandChain;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


/**
 * Class CommandChainTest
 * @package ChainCommandBundle\Tests
 */
class CommandChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandChain
     */
    private $commandChain;

    public function setUp()
    {
        $this->commandChain = new CommandChain();
    }

    public function testAddCommand()
    {
        $command = $this->createMock(ContainerAwareCommand::class);
        $command->method('getName')->willReturn('roo:hi');
        $countCommandsBefore = count($this->commandChain->getCommands());
        $this->commandChain->addCommand($command, 'foo:hello', 22);
        $countCommandsAfter = count($this->commandChain->getCommands());
        $this->assertEquals(($countCommandsBefore + 1), $countCommandsAfter);
    }

    public function testGetCommand()
    {
        $command = $this->createMock(ContainerAwareCommand::class);
        $command->method('getName')->willReturn('yoo:hi');
        $this->commandChain->addCommand($command, 'foo:hello', 22);
        $command = $this->commandChain->getCommand('yoo:hi');
        $this->assertNotEmpty($command);
        $this->assertArrayHasKey('parent', $command);
        $this->assertArrayHasKey('priority', $command);
        $this->assertEquals($command['parent'], 'foo:hello');
        $this->assertEquals($command['priority'], '22');
    }

    public function testIsMainAndIsInChain()
    {
        $command1 = $this->createMock(ContainerAwareCommand::class);
        $command1->method('getName')->willReturn('command1');
        $this->commandChain->addCommand($command1, 'main', null);

        $command2 = $this->createMock(ContainerAwareCommand::class);
        $command2->method('getName')->willReturn('command2');
        $this->commandChain->addCommand($command2, 'command1', 10);

        $this->assertEquals($this->commandChain->isMain($command1->getName()), true);
        $this->assertEquals($this->commandChain->isMain($command2->getName()), false);
        $this->assertEquals($this->commandChain->isMain('fail_name_command'), false);    // negative test

        $this->assertEquals($this->commandChain->isInChain($command1->getName()), true);
        $this->assertEquals($this->commandChain->isInChain($command2->getName()), true);
        $this->assertEquals($this->commandChain->isInChain('app:hi'), false);             // negative test
    }

    public function testGetChildrenByParent()
    {
        $command1 = $this->createMock(ContainerAwareCommand::class);
        $command1->method('getName')->willReturn('command1');
        $this->commandChain->addCommand($command1, 'main', null);

        $command2 = $this->createMock(ContainerAwareCommand::class);
        $command2->method('getName')->willReturn('command2');
        $this->commandChain->addCommand($command2, 'command1', 100);

        $command3 = $this->createMock(ContainerAwareCommand::class);
        $command3->method('getName')->willReturn('command3');
        $this->commandChain->addCommand($command3, 'command1', 12);

        $command4 = $this->createMock(ContainerAwareCommand::class);
        $command4->method('getName')->willReturn('command4');
        $this->commandChain->addCommand($command4, 'command1', 15);

        $childrenByParent = $this->commandChain->getChildrenByParent($command1->getName());
        $this->assertEquals(count($childrenByParent), 3);

        $this->assertArrayHasKey('command2', $childrenByParent);
        $this->assertArrayHasKey('command3', $childrenByParent);
        $this->assertArrayHasKey('command4', $childrenByParent);

        $this->assertEquals(array_shift($childrenByParent)['priority'], 100);
        $this->assertEquals(array_shift($childrenByParent)['priority'], 15);
        $this->assertEquals(array_shift($childrenByParent)['priority'], 12);
    }
}