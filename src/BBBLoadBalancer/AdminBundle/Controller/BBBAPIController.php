<?php

namespace BBBLoadBalancer\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;

class BBBAPIController extends Controller
{
    public function __construct(){
        // Setting for BBB api lib
        ini_set("allow_url_fopen", "On");
    }
    /**
     * @Route("/bigbluebutton/api/create", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function createAction(Request $request)
    {
        $salt = $this->container->getParameter('bbb.salt');

        $meetingID = $request->get('meetingID');
        $meeting = $this->get('meeting')->getMeetingBy(array('meetingId' => $meetingID));

        if($meeting){
            $server = $meeting->getServer();
        } else {
            $meeting = $this->get('meeting')->newMeeting();
            $server = $this->get('server')->getServerMostIdle();
        }

        $return = $this->get('bbb')->doRequest($server->getUrl() . $this->get('bbb')->cleanUri($request->getRequestUri()));

        $xml = new \SimpleXMLElement($return);

        if($xml->messageKey->__toString() != "duplicateWarning"){
            $meeting->setMeetingId($xml->meetingID->__toString());
            $meeting->setServer($server);
            $this->get('meeting')->saveMeeting($meeting);
        }

        $response = new Response($return);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Route("/bigbluebutton/api/join", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function joinAction(Request $request)
    {
        $meetingID = $request->get('meetingID');
        $meeting = $this->get('meeting')->getMeetingBy(array('meetingId' => $meetingID));

        $server = $meeting->getServer();

        $join_url = $server->getUrl() . $this->get('bbb')->cleanUri($request->getRequestUri());
        $return = $this->get('bbb')->doRequest($join_url);

        // if the return has an error message
        if(!empty($return)){
            $response = new Response($return);
            $response->headers->set('Content-Type', 'text/xml');

            return $response;
        }

        // redirect to the join url
        return $this->redirect($join_url);
    }

    /**
     * @Route("/bigbluebutton/api/isMeetingRunning", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function isMeetingRunningAction(Request $request)
    {
        $meetingID = $request->get('meetingID');
        $meeting = $this->get('meeting')->getMeetingBy(array('meetingId' => $meetingID));
        if(!$meeting){
            return new Response("<response><returncode>SUCCESS</returncode><running>false</running></response>");
        }

        $server = $meeting->getServer();

        $return = $this->get('bbb')->doRequest($server->getUrl() . $this->get('bbb')->cleanUri($request->getRequestUri()));

        $response = new Response($return);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Route("/bigbluebutton/api/end", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function endAction(Request $request)
    {
        $meetingID = $request->get('meetingID');
        $meeting = $this->get('meeting')->getMeetingBy(array('meetingId' => $meetingID));

        $server = $meeting->getServer();

        $end_url = $server->getUrl() . $this->get('bbb')->cleanUri($request->getRequestUri());
        $return = $this->get('bbb')->doRequest($end_url);

        $response = new Response($return);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Route("/bigbluebutton/api/getMeetingInfo", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function getMeetingInfoAction(Request $request)
    {
        $meetingID = $request->get('meetingID');
        $meeting = $this->get('meeting')->getMeetingBy(array('meetingId' => $meetingID));

        $server = $meeting->getServer();

        $info_url = $server->getUrl() . $this->get('bbb')->cleanUri($request->getRequestUri());
        $return = $this->get('bbb')->doRequest($info_url);

        $response = new Response($return);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
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
