<?php

require_once('oauth.php');
require_once( 'flattr_xml.php' );

class Flattr_Rest
{
	public $http_header;
	public $url;
	public $http_code;
	public $http_info;
	public $signature_method;
	public $consumer;
	public $token;

	private $apiVersion = '0.5';
	private $error;
	private $baseUrl = 'http://api.flattr.com';

	public function __construct($consumer_key, $consumer_secret, $oauth_token = null, $oauth_token_secret = null)
	{
	    if ( defined('LOCAL_DEV_ENV') )
	    {
	        $this->baseUrl = 'http://api.flattr.local';
	    }

		$this->signature_method = new OAuthSignatureMethod_HMAC_SHA1();
		$this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		if ( !empty($oauth_token) && ! empty($oauth_token_secret) )
		{
			$this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
		}
		else
		{
			$this->token = null;
		}
	}

	// Flattr API methods

	public function clickThing($id)
	{
		$result = $this->get($this->actionUrl('/thing/click/id/' . $id));
		if ( $this->http_code == 200 )
		{
			return true;
		}
		else
		{
			$this->error = "Click error " . $this->http_code . ', ' . $this->http_info . "<br />";
			return false;
		}
	}

	public function error()
	{
		return $this->error;
	}

	/**
	 * Returns an array of Flattr's categories...
	 * 
	 * @return array
	 */
	public function getCategories()
	{
		$result = $this->get($this->actionUrl('/feed/categories'));
		
		$dom = new DOMDocument();
		$dom->loadXml($result);
		$catXml = $dom->getElementsByTagName('category');
		
		return Flattr_Xml::toArray( $catXml );
	}

	/**
	 * Returns an array of clicks made by the authenticated user during the given period. 
	 *
	 * @param string $period in the format 'YYYYMM'
	 */
	public function getClicks( $period )
	{
		$response = $this->get( $this->actionUrl( "/user/clicks/period/{$period}" ) );

		if ( $this->http_code == 200 )
		{
			$dom = new DOMDocument();
			$dom->loadXml( $response );
			$clicksXml = $dom->getElementsByTagName( 'click' );
	
			return Flattr_Xml::toArray( $clicksXml );			
		}
		
		return false;
	}

	/**
	 * Returns a thing as an array.
	 * If a thing could not be found false is returned
	 * 
	 * @param string $id
	 * @return array|false
	 */
	public function getThing( $id )
	{
		$result = $this->get($this->actionUrl('/thing/get/id/' . $id));

		if ( $this->http_code == 200 )
		{
			$dom = new DOMDocument();
			$dom->loadXml($result);
			$thingXml = $dom->getElementsByTagName('thing');
			if ( ( $thingXml = $thingXml->item(0) ) !== null )
			{
			    return Flattr_Xml::toArray( $thingXml );
			}
		}

		return false;
	}

	/**
	 * Returns an array of things owned by specified user
	 * if no userid is given the current authenticated user is used.
	 *
	 * @param int $userId
	 */
	public function getThingList($userId = null)
	{
		$result = $this->get($this->actionUrl('/thing/listbyuser/id/' . $userId));
		
		$dom = new DOMDocument();
		$dom->loadXml($result);
		$thingXml = $dom->getElementsByTagName('thing');
		
		return Flattr_Xml::toArray( $thingXml );
	}

	/**
	 * Returns an array of Flattr's langauges
	 * 
	 * @return array
	 */
	public function getLanguages()
	{
		$result = $this->get($this->actionUrl('/feed/languages'));

		$dom = new DOMDocument();
		$dom->loadXml($result);
		$langXml = $dom->getElementsByTagName('language');

		return Flattr_Xml::toArray( $langXml );
	}

	/**
	 * Returns info about the specified user.
	 * If no user is given the currently authenticated user is used.
	 * 
	 * @param mixed $user string username | int userId | null 
	 * @return array|false
	 */
	public function getUserInfo($user = null)
	{
		$result = null;

		if ( !$user )
		{
			$result = $this->get($this->actionUrl('/user/me'));
		}
		else
		{
			if ( is_numeric($user) )
			{
				$result = $this->get($this->actionUrl('/user/get/id/' . $user));
			}
			else
			{
				$result = $this->get($this->actionUrl('/user/get/name/' . $user));
			}
		}

		$dom = new DOMDocument();
		$dom->loadXml($result);
		$userXml = $dom->getElementsByTagName('user');
		if ( ( $userXml = $userXml->item(0) ) !== null )
		{
		    return Flattr_Xml::toArray( $userXml );
		}

		return false;
	}
	
	/**
	 * Will register a new thing on flattr.com
	 * 
	 * @param string $url
	 * @param string $title
	 * @param string $category
	 * @param string $description
	 * @param string $tags
	 * @param string $language
	 * @param bool $hidden
	 * @param bool $temporary
	 */
	public function submitThing($url, $title, $category, $description, $tags, $language, $hidden = false, $temporary = false)
	{
		$dom = new DOMDocument('1.0', 'utf-8');
		
		$node = $dom->appendChild( $dom->createElement('thing') );
		Flattr_Xml::addElement($node, 'url', $url);
		Flattr_Xml::addElement($node, 'title', $title);
		Flattr_Xml::addElement($node, 'category', $category);
		Flattr_Xml::addElement($node, 'description', $description);
		Flattr_Xml::addElement($node, 'language', $language);
		Flattr_Xml::addElement($node, 'hidden', $hidden);
		Flattr_Xml::addElement($node, 'temporary', $temporary);
		
		$tagsNode = $node->appendChild( $dom->createElement('tags') );
		foreach ( explode(',', $tags) as $tag )
		{
		    Flattr_Xml::addElement($tagsNode, 'tag', trim($tag));
		}
		
		$result = $this->post($this->actionUrl('/thing/register'), array('data' => $dom->saveXml()));
		
		$dom = new DOMDocument();
		$dom->loadXml($result);
		$thingXml = $dom->getElementsByTagName('thing');

		return Flattr_Xml::toArray( $thingXml->item(0) );
	}
	
	// Oauth specific
	
	public function getAccessToken($verifier)
	{
		$parameters = array('oauth_verifier' => $verifier);

		$request = $this->oAuthRequest($this->accessTokenUrl(), 'GET', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		if ( isset($token['oauth_token']) && isset($token['oauth_token_secret']) )
		{
			$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
			return $token;
		}
	}
	
	public function getAuthorizeUrl($token, $access = 'read')
	{
		return $this->authorizeUrl() . '?oauth_token=' . $token['oauth_token'] . '&access_scope=' . $access;
	}
	
	/**
	 * Gets a request token from the API server and returns an oauth token.
	 *
	 * @param string $callback a callback url (fully qualified)
	 * @return array oauth response parameters as array
	 */
	public function getRequestToken($callback = null)
	{
		$parameters = array();

		if ( !empty($callback) )
		{
			$parameters['oauth_callback'] = $callback;
		}

		$response = $this->oAuthRequest($this->requestTokenUrl(), 'GET', $parameters);
		error_log($response);
		$responseParameters = OAuthUtil::parse_parameters($response);
		if ( isset($responseParameters['oauth_token']) && isset($responseParameters['oauth_token_secret']) )
		{
			$this->token = new OAuthConsumer($responseParameters['oauth_token'], $responseParameters['oauth_token_secret']);
		}
		else
		{
			$this->error = $responseParameters['oauth_problem'];
		}

		return $responseParameters;
	}
		
	// INTERNAL
	
	private function accessTokenUrl()
	{
		return $this->baseUrl . '/oauth/access_token';
	}
	
	private function actionUrl($uri)
	{
		return $this->baseUrl . '/rest/' . $this->apiVersion . $uri;
	}
		
	private function authorizeUrl()
	{
		return $this->baseUrl . '/oauth/authenticate';
	}

	private function get($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        return $response;
    }

	private function requestTokenUrl()
	{
		return $this->baseUrl . '/oauth/request_token';
	}
	
	private function getHeader($ch, $header)
	{
		$i = strpos($header, ':');
		if (!empty($i))
		{
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
  			$value = trim(substr($header, $i + 2));
  			$this->http_header[$key] = $value;
    	}
    	
    	return strlen($header);
	}
	
	private function http($url, $method, $postfields = array(), $headers = array())
	{
		$this->http_info = array();
		$ci = curl_init();

		$headers[] = 'Expect:';

		curl_setopt($ci, CURLOPT_USERAGENT, 'Flattrbot/0.1');
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ci, CURLOPT_TIMEOUT, 10);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method)
		{
  			case 'POST':
    			curl_setopt($ci, CURLOPT_POST, TRUE);
    			if (!empty($postfields))
    			{
      				curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
    			}
    			break;

  			case 'DELETE':
    			curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
    			if (!empty($postfields))
    			{
      				$url = "{$url}?{$postfields}";
    			}
		}

		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;
		curl_close ($ci);

		return $response;
	}

	private function oAuthRequest($url, $method, $parameters, $headers = array())
	{
    	if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0)
    	{
      		$url = "{$this->host}{$url}.{$this->format}";
    	}
    	
    	$request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
    	$request->sign_request($this->signature_method, $this->consumer, $this->token);
		$headers['Authorization'] = $request->to_header();

		switch ($method)
		{
			case 'GET':
  				return $this->http($request->to_url(), 'GET', null, $headers);
			default:
  				return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata(), $headers);
    	}
  	}

	private function post($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'POST', $parameters);
        return $response;
	}

}
