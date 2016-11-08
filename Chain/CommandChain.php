<?php

namespace ChainCommandBundle\Chain;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * This class add commands in chain
 * and store them
 * find children of main command
 * check if command is main or member of chain
 *
 * Class CommandChain
 * @package ChainCommandBundle\Chain
 */
class CommandChain
{
    /**
     * @var array commands in chain
     */
    private $commands;

    /**
     * CommandChain constructor.
     */
    public function __construct()
    {
        $this->commands = array();
    }

    /**
     * Add command in chain
     *
     * @param ContainerAwareCommand $command
     * @param $parent
     * @param $priority
     */
    public function addCommand(ContainerAwareCommand $command, $parent, $priority)
    {
        $this->commands[$command->getName()] = ["parent" => $parent, "priority" => $priority];
    }

    /**
     * return one command by command's name
     *
     * @param $commandName
     * @return mixed
     */
    public function getCommand($commandName)
    {
        return $this->commands[$commandName];
    }

    /**
     * return all commands that register in any chain
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * check if a command is main in chain
     *
     * @param $commandName
     * @return bool
     */
    public function isMain($commandName)
    {
        return array_key_exists($commandName, array_filter($this->commands,
            function ($item) {
                return $item['parent'] === 'main';      //return all main commands
            }));
    }

    /**
     * check whether command is member of chain
     *
     * @param $commandName
     * @return bool
     */
    public function isInChain($commandName)
    {
        return array_key_exists($commandName, $this->commands);
    }

    /**
     * return children of main command ordered by priority DESC
     *
     * @param $commandName
     * @return array
     */
    public function getChildrenByParent($commandName)
    {
        //find children of main command
        $childrenByParent = array_filter($this->commands,
            function ($item) use ($commandName) {
                return $item['parent'] === $commandName;
            });
        //create array priorities
        foreach ($childrenByParent as $key => $value) {
            $priorities[] = $value['priority'];
        }
        //order children DESC by priority
        array_multisort($priorities, SORT_DESC, $childrenByParent);

        return $childrenByParent;
    }
}