<?php

namespace BBBLoadBalancer\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use BBBLoadBalancer\UserBundle\Annotations\ValidAPIKey;

class DefaultAPIController extends Controller
{
    /**
     * @Route("/api/status", name="status", defaults={"_format": "json"})
     * @Method({"GET"})
     * @ValidAPIKey
     */
    public function statusAction(Request $request)
    {
        $return = array(
            'servers' => array()
        );

        // return all servers
        $servers = $this->get('server')->getServersBy(array("enabled" => true, "up" => true));
        if(empty($servers)){
            return new JsonResponse(array(
                "status" => "ERROR",
                "message" => "No Servers found that are enabled and up."
                )
            );
        }

        $servercount = count($servers);

        return new JsonResponse(array(
                "status" => "OK",
                "message" => "$servercount servers are used by the load balancer."
            )
        );
    }
}