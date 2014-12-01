<?php

namespace BBBLoadBalancer\AdminBundle\Service;

use BBBLoadBalancer\AdminBundle\Document\Server;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \DateTime;
use \DateTimeZone;

class ServerService
{
    protected $dm;

    /**
     * Constructor.
     */
    public function __construct($dm)
    {
        $this->dm = $dm->getManager();
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
}
