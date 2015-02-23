<?php

namespace BBBLoadBalancer\AdminBundle\Service;

class BBBService
{
	protected $salt;

	/**
     * Constructor.
     */
    public function __construct($salt)
    {
        $this->salt = $salt;

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

        return $output;
	}

	public function cleanUri($uri){
		// remove dev url
		return str_replace("app_dev.php/", "", $uri);
	}

}
