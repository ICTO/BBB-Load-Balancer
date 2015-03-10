<?php

namespace BBBLoadBalancer\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupMeetingsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bbblb:meetings:cleanup')
            ->setDescription('Remove meeting mapping when they stopped on the BBB server.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $servers = $this->getContainer()->get('server')->getServersBy(array());

        $active_meetings = array();
        foreach ($servers as $server) {
            $server_meetings = $this->getContainer()->get('bbb')->getMeetings($server);
            if($server_meetings){
                foreach($server_meetings as $server_meeting){
                    $active_meetings[] = $server_meeting['id'];
                }
            }
        }
        $meetings = $this->getContainer()->get('meeting')->getMeetingsBy(array());
        foreach($meetings as $meeting){
            if(!in_array($meeting->getMeetingId(), $active_meetings)){
                $this->getContainer()->get('meeting')->removeMeeting($meeting);
            }
        }
    }
}