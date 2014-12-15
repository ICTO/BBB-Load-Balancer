<?php

namespace BBBLoadBalancer\UserBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader;//This thing read annotations
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;//Use essential kernel component
use Teamspot\AccessBundle\Annotations;//Use our annotation
use Symfony\Component\HttpFoundation\Response;// For example I will throw 403, if access denied
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use BBBLoadBalancer\UserBundle\Annotations\ValidAPIKey;

class AnnotationDriver{

    protected $reader;
    protected $access;
    protected $request;
    protected $user;

    public function __construct($reader, $request, $user)
    {
        $this->reader = $reader;
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * This event will fire during any controller call
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) { //return if no controller
            return;
        }

        $object = new \ReflectionObject($controller[0]);// get controller
        $method = $object->getMethod($controller[1]);// get method

        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
            if($configuration instanceof ValidAPIKey){
                $key = $this->request->headers->get('API-Key');
                $user = $this->user->getUserBy(array('apiKey' => $key));
                if(!$user){
                    throw new AccessDeniedHttpException();
                }
            }
        }
    }
}