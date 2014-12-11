<?php

namespace BBBLoadBalancer\AdminBundle\Service;

use BBBLoadBalancer\AdminBundle\Document\Server;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServerService
{
    protected $dm;
    protected $bbb;

    /**
     * Constructor.
     */
    public function __construct($dm, $bbb)
    {
        $this->dm = $dm->getManager();
        $this->bbb = $bbb;
    }

    /**
     * Get server by id
     */
    public function getServerById($id){
        return $this->dm->getRepository('BBBLoadBalancerAdminBundle:Server')->find($id);
    }

    /**
     * Get servers by
     */
    public function getServersBy($args, $orders = array(), $limit = null, $skip = null){
        return $this->dm->getRepository('BBBLoadBalancerAdminBundle:Server')->findBy($args, $orders, $limit, $skip);
    }

    /**
     * Get server by args
     */
    public function getServerBy($args){
        return $this->dm->getRepository('BBBLoadBalancerAdminBundle:Server')->findOneBy($args);
    }

    /**
     * Save the server
     */
    public function saveServer($server){
        $this->dm->persist($server);
        $this->dm->flush();
    }

    /**
     * remove the server
     */
    public function removeServer($server){
        if (!$server) {
            throw new NotFoundHttpException();
        }

        $this->dm->remove($server);
        $this->dm->flush();
    }

    /**
     * New server
     */
    public function newServer(){
        $server = new Server();
        return $server;
    }

    /**
     * Get server most idle
     */
    public function getServerMostIdle(){
        $servers = $this->getServersBy(array('enabled' => true));
        $best_server = array(
            'server' => false,
            'count_meetings' => false,
        );
        foreach($servers as $server) {
            $meetings = $this->bbb->getMeetings($server);
            $count = 0;
            if(is_array($meetings)){
                foreach($meetings as $meeting){
                    if($meeting['running']){
                        $count + 1;
                    }
                }
                if($best_server['count_meetings'] === false || $best_server['count_meetings'] > $count){
                    $best_server = array(
                        'server' => $server,
                        'count_meetings' => $count,
                    );
                }
            }
        }

        return $best_server['server'];
    }
}
