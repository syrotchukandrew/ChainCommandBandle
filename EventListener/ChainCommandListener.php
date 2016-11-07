<?php

namespace ChainCommandBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use ChainCommandBundle\Chain\CommandChain;
use Symfony\Bridge\Monolog\Logger;

/**
 * This listener is running on console command
 * check whether command is in chain(return for command not in chain)
 * check whether command is main in chain(error for not main command)
 * if command is main run it and members of this chain
 *
 * Class ChainCommandListener
 * @package ChainCommandBundle\EventListener
 */
class ChainCommandListener
{
    /**
     * @var CommandChain
     */
    private $commandChain;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * ChainCommandListener constructor.
     * @param CommandChain $commandChain
     * @param Logger $logger
     */
    public function __construct(CommandChain $commandChain, Logger $logger)
    {
        $this->commandChain = $commandChain;
        $this->logger = $logger;
    }

    /**
     * @param ConsoleCommandEvent $event
     * @throws \Exception
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        $commandName = $command->getName();
        $output = $event->getOutput();
        $input = $event->getInput();

        //return if command not in chain
        if (!$this->commandChain->isInChain($commandName)) {
            return;
        }

        //error and disabling if command is not main
        if (!$this->commandChain->isMain($commandName)) {
            $output->writeln(sprintf(
                "Error: %s command is a member of %s command chain and cannot be executed on its own.",
                $commandName,
                $this->commandChain->getCommand($commandName)['parent']
            ));
            $event->disableCommand();
            return;
        }

        $this->logger->info(sprintf(
            '%s is a master command of a command chain that has registered member commands',
            $commandName
        ));
        $childrenCommands = $this->commandChain->getChildrenByParent($commandName); //get all children

        //loging info about all children in this chain
        foreach ($childrenCommands as $child => $args) {
            $this->logger->info(sprintf(
                '%s registered as a member of %s command chain',
                $child,
                $commandName
            ));
        }

        $this->logger->info(sprintf(
            'Executing %s command itself first:',
            $commandName
        ));
        $event->disableCommand();
        $command->run($input, $output);         //Executing main command itself first

        if (!empty($childrenCommands)) {
            $this->logger->info(sprintf(
                'Executing %s chain members:',
                $commandName
            ));
            $application = $command->getApplication();
            //Executing main chain members
            foreach ($childrenCommands as $childCommandName => $values) {
                try {
                    $childCommand = $application->get($childCommandName);
                    $childCommand->run($input, $output);
                } catch (\Exception $exception) {
                    $this->logger->error($exception->getMessage());
                    throw $exception;
                }
            }
        }
        $this->logger->info(sprintf(
            'Execution of %s chain completed.',
            $commandName
        ));

        return;
    }
}