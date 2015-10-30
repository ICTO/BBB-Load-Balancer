<?php

namespace BBBLoadBalancer\BBBBundle\Service;

class BBBService
{
	protected $salt;

	/**
     * Constructor.
     */
    public function __construct($salt, $logger)
    {
        $this->salt = $salt;
        $this->logger = $logger;

        // Setting for BBB api lib
        ini_set("allow_url_fopen", "On");
    }

	public function getMeetings($server){
        $result = $this->doRequest($server->getUrl()."/bigbluebutton/api/"."getMeetings".'?checksum='.sha1("getMeetings".$this->salt));
        $meetings = array();
        if($result){
            $xml = new \SimpleXMLElement($result);
            if($xml->returncode == "SUCCESS"){
            	if($xml->meetings->meeting->count()) {
	            	foreach($xml->meetings->meeting as $meeting){
	            		$dt = new \DateTime('@' . round($meeting->createTime/1000));
	            		$meetings[] = array(
	            			'id' => $meeting->meetingID->__toString(),
	            			'name' => $meeting->meetingName->__toString(),
	            			'created' => $dt->format('c'),
	            			'running' => $meeting->running->__toString()
	            		);
	            	}
	            }
            }

            return $meetings;
        } else {
            return false;
        }
	}

	public function doRequest($url, $timeout = 2){
        if (!function_exists('curl_init')){
			throw new \Exception('Sorry cURL is not installed!');
        }

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_USERAGENT, 'Curios');
        curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $output = curl_exec($ch);

        curl_close($ch);

        $this->logger->debug("Request to BBB Server", array("url" => $url, "output" => $output));

        return $output;
	}

    public function doPostRequest($url, $data, $timeout = 10)
    {
        $headers = array(
            "Content-type: text/xml",
            "Content-length: " . strlen($xml),
            "Connection: close",
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        return $response;
    }

	public function cleanUri($uri){
		// remove dev url
		return str_replace("app_dev.php/", "", $uri);
	}

}
