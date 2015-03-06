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
    /**
     * @Route("/bigbluebutton/api", defaults={"_format": "xml"})
     * @Method({"GET"})
     */
    public function statusAction(Request $request)
    {
    	$servers = $this->get('server')->getServersBy(array("enabled" => true, "up" => true));
    	if($servers){
    		$return = "<response>
    					   <returncode>SUCCESS</returncode>
    					   <version></version>
					   </response>";
    	} else {
    		$return = "<response>
		    			   <returncode>FAILED</returncode>
			               <messageKey>noBBBServersActive</messageKey>
			               <message>The BBB Load balancer has no available BBB servers.</message>
		               </response>";
    	}

        $response = new Response($return);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
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

        $save = false;

        if($meeting){
            $server = $meeting->getServer();
        } else {
            $meeting = $this->get('meeting')->newMeeting();
            $server = $this->get('server')->getServerMostIdle();
            $save = true;
        }

        $return = $this->get('bbb')->doRequest($server->getUrl() . $this->get('bbb')->cleanUri($request->getRequestUri()));

        if(!$return){
            return $this->errorResponse($server);
        }

        $xml = new \SimpleXMLElement($return);

        if($save){
            $meeting->setMeetingId($xml->meetingID->__toString());
            $meeting->setServer($server);
            $this->get('meeting')->saveMeeting($meeting);
            $this->get('logger')->info("Created new meeting.", array("Server ID" => $server->getId(), "Server URL" => $server->getUrl(), "Meeting ID" => $meeting->getId(), "BBB meeting ID" => $meeting->getMeetingId()));
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
        if(!$meeting){
            return $this->errorMeeting($meetingID);
        }

        $server = $meeting->getServer();

        $join_url = $server->getUrl() . $this->get('bbb')->cleanUri($request->getRequestUri());
        $return = $this->get('bbb')->doRequest($join_url);

        if($return === false){
            return $this->errorResponse($server);
        }

        // if the return has an error message
        if(!empty($return)){
            $response = new Response($return);
            $response->headers->set('Content-Type', 'text/xml');

            return $response;
        }

        $this->get('logger')->info("Joining meeting.", array("Server ID" => $server->getId(), "Server URL" => $server->getUrl(), "Meeting ID" => $meeting->getId(), "BBB meeting ID" => $meeting->getMeetingId()));

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
            $response = new Response("
                <response>
                    <returncode>SUCCESS</returncode>
                    <running>false</running>
                </response>");
            $response->headers->set('Content-Type', 'text/xml');
            return $response;
        }

        $server = $meeting->getServer();

        $return = $this->get('bbb')->doRequest($server->getUrl() . $this->get('bbb')->cleanUri($request->getRequestUri()));

        if(!$return){
            return $this->errorResponse($server);
        }

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
        if(!$meeting){
            return $this->errorMeeting($meetingID);
        }

        $server = $meeting->getServer();

        $end_url = $server->getUrl() . $this->get('bbb')->cleanUri($request->getRequestUri());
        $return = $this->get('bbb')->doRequest($end_url);

        if(!$return){
            return $this->errorResponse($server);
        }

        $this->get('logger')->info("Ending meeting.", array("Server ID" => $server->getId(), "Server URL" => $server->getUrl(), "Meeting ID" => $meeting->getId(), "BBB meeting ID" => $meeting->getMeetingId()));

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
        if(!$meeting){
            return $this->errorMeeting($meetingID);
        }

        $server = $meeting->getServer();

        $info_url = $server->getUrl() . $this->get('bbb')->cleanUri($request->getRequestUri());
        $return = $this->get('bbb')->doRequest($info_url);

        if(!$return){
            return $this->errorResponse($server);
        }

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
        $servers = $this->get('server')->getServersBy(array('enabled' => true));
        $meetings_xml = "";
        foreach($servers as $server){
            $meetings_url = $server->getUrl() . $this->get('bbb')->cleanUri($request->getRequestUri());
            $return = $this->get('bbb')->doRequest($meetings_url);

            if(!$return){
                $this->get('logger')->error("Server did not respond.", array("Server_id" => $server->getId(), "Server URL" => $server->getUrl()));
            }
            else {
                $xml = new \SimpleXMLElement($return);
                if(!empty($xml->meetings)){
                    foreach($xml->meetings as $meeting){
                        $meetings_xml .= $meeting->meeting->asXML();
                    }
                }
            }
        }

        if(empty($meetings_xml)){
            $response = new Response("
                <response>
                    <returncode>SUCCESS</returncode>
                    <meetings/>
                    <messageKey>noMeetings</messageKey>
                    <message>no meetings were found</message>
                </response>");
            $response->headers->set('Content-Type', 'text/xml');

            return $response;
        }

        $response = new Response("
            <response>
                <returncode>SUCCESS</returncode>
                <meetings>" . $meetings_xml . "</meetings>
            </response>");
        $response->headers->set('Content-Type', 'text/xml');

        return $response;

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

    /**
     * return error response
     */
    private function errorResponse($server){
        $this->get('logger')->error("Server did not respond.", array("Server ID" => $server->getId(), "Server URL" => $server->getUrl()));

        $this->get('server')->updateServerUpStatus($server);

        $response = new Response("
            <response>
                <returncode>FAILED</returncode>
                <messageKey>connectionError</messageKey>
                <message>could not connect to the server</message>
            </response>");
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }

    /**
     * return error response
     */
    private function errorMeeting($meeting_id){
        $this->get('logger')->error("Meeting ID was not found.", array("Meeting ID" => $meeting_id));

        $response = new Response("
            <response>
                <returncode>FAILED</returncode>
                <messageKey>meetingIdError</messageKey>
                <message>could not found meeting</message>
            </response>");
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }
}
