<?php

namespace ChainCommandBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $chain = $this->container->get('chain_command.command_chain');
        $commands = $chain->getComands();
        $command = $commands[0];
        echo $command;
        return $this->render('ChainCommandBundle:Default:index.html.twig');
    }
}
