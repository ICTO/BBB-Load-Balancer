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

class ServerAPIController extends Controller
{
    /**
     * @Route("api/servers", name="servers", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function serversAction(Request $request)
    {
        $return = array(
            'servers' => array()
        );

        // return all users
        $servers = $this->get('server')->getServersBy(array());
        foreach($servers as $server){
            $return['servers'][] = array(
                'id' => $server->getId(),
                'name' => $server->getName(),
                'url' => $server->getURL(),
                'up' => false,
                'enabled' => $server->getEnabled()
            );
        }

        return new JsonResponse($return);
    }

    /**
     * @Route("api/servers", name="add_server", defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function addServerAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $server = $this->get('server')->newServer();

        $server->setName($data['server']['name']);
        $server->setURL($data['server']['url']);
        $server->setEnabled($data['server']['enabled']);

        // validate server
        $errors = $this->get('validator')->validate($server);
        if($errors->count()){
            foreach($errors as $error){
                throw new ValidatorException($error->getMessage());
            }
        }

        $this->get('server')->saveServer($server);

        $return['user'] = array(
            'id' => $server->getId(),
            'name' => $server->getName(),
            'url' => $server->getURL(),
            'enabled' => $server->getEnabled(),
            'up' => true,
        );

        return new JsonResponse($return);
    }

    /**
     * @Route("api/servers/{id}", name="edit_server", defaults={"_format": "json"})
     * @Method({"PUT"})
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

        // validate server
        $errors = $this->get('validator')->validate($server);
        if($errors->count()){
            foreach($errors as $error){
                throw new ValidatorException($error->getMessage());
            }
        }

        $this->get('server')->saveServer($server);

        $return['server'] = array(
            'id' => $server->getId(),
            'name' => $server->getName(),
            'url' => $server->getURL(),
            'enabled' => $server->getEnabled(),
            'up' => true,
        );

        return new JsonResponse($return);
    }

    /**
     * @Route("api/servers/{id}", name="remove_server", defaults={"_format": "json"})
     * @Method({"DELETE"})
     */
    public function removeServerAction(Request $request, $id)
    {
        $server = $this->get('server')->getServerById($id);

        $data = json_decode($request->getContent(), true);

        if(!$server){
            throw new NotFoundHttpException("Server not found");
        }

        $this->get('server')->removeServer($server);

        return new JsonResponse(array());
    }
}