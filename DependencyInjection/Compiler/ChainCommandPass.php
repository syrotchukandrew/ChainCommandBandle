<?php

namespace ChainCommandBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class find all services with tag "chain_command" and
 * add them to ChainCommand service
 */
class ChainCommandPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has('chain_command.command_chain')) {
            return;
        }

        $definition = $container->findDefinition('chain_command.command_chain');

        // find all service IDs with the chain_command tag
        $taggedServices = $container->findTaggedServiceIds('chain_command');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                // add the chain command to the ChainCommand service
                $definition->addMethodCall('addCommand', array(
                    new Reference($id),
                    $attributes["parent"],    //add main function if exist for function member of chain
                    $attributes["priority"]   //priority in chain
                ));
            }
        }
    }
}