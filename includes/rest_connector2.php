<?php
/*
 * Created on Apr 6, 2012
 * 
 * We need to load the HTTP_Request2 package
 *
 */

class RESTConnector
{
    private $user_agent = 'com.acme.basicwidget/1.0';
	private $privateID = 'X-PAPPID: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
	private $username;
	private $password;
    private $url;
	private $domain;
	private $method;
	private $requestXml;
    private $httperror = "";
    private $exception = "";
    private $responseBody = "";
    private $headers = "";
    private $cookieJar = "";
    private $req = null;
    private $res = null;
	private $arrCurl;

    
    public function __construct($root_url = "")
    {
	    $config = require(dirname(__FILE__).'/../config/main.php');
	    $this->domain = sprintf(
		    "https://%s:%d/api/",
		    $config['lightspeedServer'],
		    $config['lightspeedPort']
	    );
	    $this->username = $config['lightspeedUser'];
	    $this->password = $config['lightspeedPass'];
	    $this->arrCurl = array(
		    'CURLOPT_HTTPAUTH' => CURLAUTH_BASIC,
		    'CURLOPT_SSL_VERIFYPEER' => false,
		    'CURLOPT_SSL_VERIFYHOST' => false,
		    'CURLOPT_USERAGENT' => $this->user_agent,
		    'CURLOPT_HTTPHEADER' => array($this->privateID),
		    'CURLOPT_ENCODING' => 'gzip',
		    'CURLOPT_USERPWD' => $this->username . ':' . $this->password,
		    'CURLOPT_RETURNTRANSFER' => true,
		    'CURLOPT_HEADER' => 1,
		    'CURLINFO_HEADER_OUT' => true,
		    'CURLOPT_FOLLOWLOCATION' => true
	    );
	    return true;
    }
    
    public function createRequest($url, $method = 'GET', $body = null, $mycookies = null)
    {
	    $this->url = $this->domain . $url;
	    $this->requestXml = $body;
	    $this->cookieJar = $mycookies;

    	switch ($method)
	    {
        	case "GET":
            case "POST":
            case "PUT":
            case "LOCK":
            case "UNLOCK":
//		    case "DELETE":
		        $this->method = $method;
		        $this->arrCurl['CURLOPT_CUSTOMREQUEST'] = $this->method;
		        break;

            default:
				throw new Exception ($method . ' method not supported');
	           	break;
        }
    }

	/**
	 * Deprecated function. Will eventually be removed or updated
	 */
	private function setPostBody($data) {
        if ($data != null) {
            $this->req->setBody($data);
        }
    }

	/**
	 * Deprecated function. Will eventually be removed or updated
	 */
	public function addHeader($header, $value) {
    	if ($header != null &&  $value != null) {
    		$this->req->setHeader($header, $value);
    	}
    }
    
    public function sendRequest()
    {
	    $ch = curl_init($this->url);

	    if (!$ch)
		    throw new Exception('Failed to initialize curl resource');

	    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore certificate check errors
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Ignore host check errors
	    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->privateID));
	    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
	    curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);

	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);

	    $fp = fopen(dirname(__FILE__).'/../logs/errorlog.txt', 'w');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_VERBOSE, true);
	    curl_setopt($ch, CURLOPT_HEADER, 1);
	    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_STDERR, $fp);
	    if (!is_null($this->cookieJar))
	        curl_setopt($ch, CURLOPT_COOKIE, $this->cookieJar);

	    $result = curl_exec($ch);
	    $arrInfo = curl_getinfo($ch);

	    if (!$result)
		    throw new Exception(sprintf("cURL call failed\n%s\n%s", curl_error($ch), curl_errno($ch)));

	    if (!isset($arrInfo['http_code']))
		    throw new Exception(sprintf("An error occurred\n %s", $result."\n".print_r($arrInfo, true)));

	    if ((integer)$arrInfo['http_code'] < 200 || (integer)$arrInfo['http_code'] > 206)
	    {
		    $this->setError($result);
		    echo sprintf("Unexpected HTTP Status: %s\n", $this->httperror);
		    var_dump($result);
		    exit();
	    }

	    $this->setResponse($result);
	    $this->setCookies($result);
    }

    public function getResponse() {
        return $this->responseBody;
    }

	public function setResponse($result)
	{
		if (is_null($result))
			$this->responseBody = null;
		else
			$this->responseBody = substr($result, strpos($result, '<?xml'));
	}
    
    public function getError() {
        return $this->httperror;
    }

	public function setError($result)
	{
		$endPos = strpos($result, 'content-type');
		$startPos = strpos($result, 'HTTP/1.1 ') + strlen('HTTP/1.1 ');
		$lengthError = $endPos - $startPos;
		$this->httperror = substr($result, $startPos, $lengthError);
	}
    
    public function getException() {
        return $this->exception;
    }

	public function setCookies($result)
	{
		$endPos = strpos($result, '; Path');
		$startPos = strpos($result, 'LS_SERVER_SESSION_ID=');
		$lengthCookie = $endPos - $startPos;
		$this->cookieJar = substr($result, $startPos, $lengthCookie);
	}

    public function getCookies()
    {
	    return $this->cookieJar;
    }
}

?>
