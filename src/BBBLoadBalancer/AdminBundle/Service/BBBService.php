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
    }

	public function getMeetings($server){
		$bbb = new \BigBlueButton($this->salt, $server->getUrl() . "/bigbluebutton/");
		try {
			$result = $bbb->getMeetingsWithXmlResponseArray();
			if ($result['returncode'] == 'SUCCESS') {
				return array_slice($result, 3);
			}
			else {
				return false;
			}
		}
		catch (\Exception $e) {
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
