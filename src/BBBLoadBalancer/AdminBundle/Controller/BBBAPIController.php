<?php

namespace BBBLoadBalancer\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class BBBAPIController extends Controller
{
    public function __construct(){
        ini_set("allow_url_fopen", "On");
    }
    /**
     * @Route("/bigbluebutton/api/create", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function createAction(Request $request)
    {
        $salt = $this->container->getParameter('bbb.salt');
        $bbb = new \BigBlueButton("test", "baseurl");
        // @TODO : not yet supported
    }

    /**
     * @Route("/bigbluebutton/api/join", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function joinAction(Request $request)
    {
        // @TODO : not yet supported
    }

    /**
     * @Route("/bigbluebutton/api/isMeetingRunning", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function isMeetingRunningAction(Request $request)
    {
        // @TODO : not yet supported
    }

    /**
     * @Route("/bigbluebutton/api/end", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function endAction(Request $request)
    {
        // @TODO : not yet supported
    }

    /**
     * @Route("/bigbluebutton/api/getMeetingInfo", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function getMeetingInfoAction(Request $request)
    {
        // @TODO : not yet supported
    }

    /**
     * @Route("/bigbluebutton/api/getMeetings", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function getMeetingsAction(Request $request)
    {
        // @TODO : not yet supported
    }

    /**
     * @Route("/bigbluebutton/api/getRecordings", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function getRecordingsAction(Request $request)
    {
        // @TODO : not yet supported
    }

    /**
     * @Route("/bigbluebutton/api/publishRecordings", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function publishRecordingsAction(Request $request)
    {
        // @TODO : not yet supported
    }

    /**
     * @Route("/bigbluebutton/api/deleteRecordings", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function deleteRecordingsAction(Request $request)
    {
        // @TODO : not yet supported
    }

    /**
     * @Route("/bigbluebutton/api/getDefaultConfigXML.xml", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function getDefaultConfigXMLAction(Request $request)
    {
        // @TODO : not yet supported
    }

    /**
     * @Route("/bigbluebutton/api/setConfigXML.xml", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function setConfigXMLAction(Request $request)
    {
        // @TODO : not yet supported
    }
}
