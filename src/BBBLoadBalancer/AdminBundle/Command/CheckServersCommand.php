<?php

namespace BBBLoadBalancer\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckServersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bbblb:servers:check')
            ->setDescription('Enable servers that are up and disable servers that are down.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $servers = $this->getContainer()->get('server')->getServersBy(array());
        foreach ($servers as $server) {
            $this->getContainer()->get('server')->updateServerUpStatus($server);
        }
    }
}