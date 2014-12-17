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
            $up = false;
            $result = $this->getContainer()->get('bbb')->doRequest($server->getUrl()."/bigbluebutton/api");
            if($result) {
                $xml = new \SimpleXMLElement($result);
                if($xml->returncode == "SUCCESS"){
                    $up = true;
                }
            }

            // server is up but not enabled, enable server
            if($up && !$server->getEnabled()){
                $server->setEnabled(true);
                $this->getContainer()->get('server')->saveServer($server);
                $this->getContainer()->get('logger')->info("Enabled Server.", array("Server_id" => $server->getId(), "Server URL" => $server->getUrl()));
            }

            // server is down and is enabled, disable server
            if(!$up && $server->getEnabled()){
                $server->setEnabled(false);
                $this->getContainer()->get('server')->saveServer($server);
                $this->getContainer()->get('logger')->info("Disabled Server.", array("Server_id" => $server->getId(), "Server URL" => $server->getUrl()));
            }
        }
    }
}