<?php

namespace BBBLoadBalancer\AdminBundle\Service;

use BBBLoadBalancer\AdminBundle\Document\Server;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidatorException;

class ServerService
{
    protected $dm;
    protected $bbb;
    protected $validator;
    protected $meeting;
    protected $logger;

    /**
     * Constructor.
     */
    public function __construct($dm, $bbb, $validator, $meeting, $logger)
    {
        $this->dm = $dm->getManager();
        $this->bbb = $bbb;
        $this->validator = $validator;
        $this->meeting = $meeting;
        $this->logger = $logger;
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
        // validate server
        $errors = $this->validator->validate($server);
        if($errors->count()){
            foreach($errors as $error){
                throw new ValidatorException($error->getMessage(), 406);
            }
        }
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

        // first remove all meetings attached to the server
        $this->meeting->removeMeetingsFromServer($server);

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
        $servers = $this->getServersBy(array('enabled' => true, 'up' => true));
        $best_server = array(
            'server' => false,
            'count_meetings' => false,
        );
        foreach($servers as $server) {
            $meetings = $this->bbb->getMeetings($server);
            $count = 0;
            if($meetings === false){
                // check if server is down.
                $this->updateServerUpStatus($server);
            }
            else if(is_array($meetings)){
                foreach($meetings as $meeting){
                    //if($meeting['running']->__toString() == "true"){
                        $count++;
                    //}
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

    /**
     * Update server upstatus
     */
    public function updateServerUpStatus(&$server){
        $before_up = $server->getUp();
        $up = false;
        $result = $this->bbb->doRequest($server->getUrl()."/bigbluebutton/api");
        if($result) {
            $xml = new \SimpleXMLElement($result);
            if($xml->returncode == "SUCCESS"){
                $up = true;
            }
        }
        if($up != $before_up){
            $server->setUp($up);
            $this->saveServer($server);

            if($up){
                $this->logger->info("Server is up.", array("Server_id" => $server->getId(), "Server URL" => $server->getUrl()));
            }
            else {
                $this->logger->info("Server is down.", array("Server_id" => $server->getId(), "Server URL" => $server->getUrl()));
            }
        }
    }
}
