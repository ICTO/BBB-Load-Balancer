<?php

namespace BBBLoadBalancer\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidatorException;
use BBBLoadBalancer\UserBundle\Annotations\ValidAPIKey;

class ServerAPIController extends Controller
{
    /**
     * @Route("/api/servers", name="servers", defaults={"_format": "json"})
     * @Method({"GET"})
     * @ValidAPIKey
     */
    public function serversAction(Request $request)
    {
        $return = array(
            'servers' => array()
        );

        // return all users
        $servers = $this->get('server')->getServersBy(array());
        foreach($servers as $server){
            // try to connect to server
            $up = false;
            try {
                $result = $this->get('bbb')->doRequest($server->getUrl()."/bigbluebutton/api");
                $xml = new \SimpleXMLElement($result);
                if($xml->returncode == "SUCCESS"){
                    $up = true;
                }
                else {
                    $this->get('logger')->error("Server did not respond.", array("Server_id" => $server->getId(), "Server URL" => $server->getUrl()));
                }
            } catch (\Exception $e) {}

            $return['servers'][] = array(
                'id' => $server->getId(),
                'name' => $server->getName(),
                'url' => $server->getURL(),
                'up' => $up,
                'enabled' => $server->getEnabled()
            );
        }

        return new JsonResponse($return);
    }

    /**
     * @Route("/api/servers", name="add_server", defaults={"_format": "json"})
     * @Method({"POST"})
     * @ValidAPIKey
     */
    public function addServerAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $server = $this->get('server')->newServer();

        $server->setName($data['server']['name']);
        $server->setURL($data['server']['url']);
        $server->setEnabled($data['server']['enabled']);

        $this->get('server')->saveServer($server);
        $this->get('logger')->info("Server added.", array("Server ID" => $server->getId(), "Server URL" => $server->getUrl()));

        // try to connect to server
        $up = false;
        try {
            $result = $this->get('bbb')->doRequest($server->getUrl()."/bigbluebutton/api");
            $xml = new \SimpleXMLElement($result);
            if($xml->returncode == "SUCCESS"){
                $up = true;
            }
            else {
                $this->get('logger')->error("Server did not respond.", array("Server_id" => $server->getId(), "Server URL" => $server->getUrl()));
            }
        } catch (\Exception $e) {}

        $return['server'] = array(
            'id' => $server->getId(),
            'name' => $server->getName(),
            'url' => $server->getURL(),
            'enabled' => $server->getEnabled(),
            'up' => $up,
        );

        return new JsonResponse($return);
    }

    /**
     * @Route("/api/servers/{id}", name="edit_server", defaults={"_format": "json"})
     * @Method({"PUT"})
     * @ValidAPIKey
     */
    public function editServerAction(Request $request, $id)
    {
        $server = $this->get('server')->getServerById($id);

        $data = json_decode($request->getContent(), true);

        if(!$server){
            throw new NotFoundHttpException("Server not found");
        }

        $server->setName($data['server']['name']);
        $server->setURL($data['server']['url']);
        $server->setEnabled($data['server']['enabled']);

        $this->get('server')->saveServer($server);
        $this->get('logger')->info("Server edited.", array("Server ID" => $server->getId(), "Server URL" => $server->getUrl()));

        // try to connect to server
        $up = false;
        try {
            $result = $this->get('bbb')->doRequest($server->getUrl()."/bigbluebutton/api");
            $xml = new \SimpleXMLElement($result);
            if($xml->returncode == "SUCCESS"){
                $up = true;
            }
        } catch (\Exception $e) {}

        $return['server'] = array(
            'id' => $server->getId(),
            'name' => $server->getName(),
            'url' => $server->getURL(),
            'enabled' => $server->getEnabled(),
            'up' => $up,
        );

        return new JsonResponse($return);
    }

    /**
     * @Route("/api/servers/{id}", name="remove_server", defaults={"_format": "json"})
     * @Method({"DELETE"})
     * @ValidAPIKey
     */
    public function removeServerAction(Request $request, $id)
    {
        $server = $this->get('server')->getServerById($id);

        $data = json_decode($request->getContent(), true);

        if(!$server){
            throw new NotFoundHttpException("Server not found");
        }

        $this->get('logger')->info("Server removed.", array("Server ID" => $server->getId(), "Server URL" => $server->getUrl()));
        $this->get('server')->removeServer($server);

        return new JsonResponse(array());
    }

    /**
     * @Route("/api/meetings", name="meetings", defaults={"_format": "json"})
     * @Method({"GET"})
     * @ValidAPIKey
     */
    public function meetingsAction(Request $request)
    {
        $server = $this->get('server')->getServerById($request->get('server_id'));

        $return = array(
            'meetings' => array()
        );

        $meetings = $this->get('bbb')->getMeetings($server);

        // return all meetings with info
        foreach($meetings as $meeting){
            $dt = new \DateTime('@' . round($meeting['createTime']->__toString()/1000));
            $return['meetings'][] = array(
                'id' => $meeting['meetingId']->__toString(),
                'name' => $meeting['meetingName']->__toString(),
                'created' => $dt->format('c'),
                'running' => $meeting['running']->__toString()
            );
        }

        return new JsonResponse($return);
    }
}