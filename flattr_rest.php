<?php

class Flattr_Rest
{
	public $http_header;
	public $url;
	public $http_code;
	public $http_info;
	public $signature_method;
	public $consumer;
	public $token;

	private $apiVersion = '0.0.1';
	private $error;
	private $baseUrl = 'http://api.flattr.local';

	public function error()
	{
		return $this->error;
	}

	private function actionUrl($uri)
	{
		return $this->baseUrl . '/rest/' . $this->apiVersion . $uri;
	}

	private function accessTokenUrl()
	{
		return $this->baseUrl . '/oauth/access_token';
	}
	
	private function authorizeUrl()
	{
		return $this->baseUrl . '/oauth/authenticate';
	}

	private function connectUrl()
	{
		return $this->baseUrl . '/oauth/connect';
	}

	private function requestTokenUrl()
	{
		return $this->baseUrl . '/oauth/request_token';
	}

	private function get($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        return $response;
    }
    
	private function post($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'POST', $parameters);
        return $response;
	}

	public function browse($params)
	{
		$url = $this->actionUrl('/thing/browse');
        if ( isset($params['query']) && $params['query'] != '' )
        {   
            $url .= '/query/' . $params['query'];
        }   
        if ( isset($params['tag']) && $params['tag'] != '' )
        {   
            if ( ! is_array($params['tag']) )
            {   
                $params['tag'] = array($params['tag']);
            }   
            $url .= '/tag/' . implode(',', $params['tag']);
        }   
        if ( isset($params['category']) && $params['category'] != '' )
        {   
            if ( ! is_array($params['category']) )
            {   
                $params['category'] = array($params['category']);
            }   
            $url .= '/category/' . implode(',', $params['category']);
        }   
        if ( isset($params['language']) && $params['language'] != '' )
        {
            if ( ! is_array($params['language']) )
            {
                $params['language'] = array($params['language']);
            }
            $url .= '/language/' . implode(',', $params['language']);
        }
        if ( isset($params['user']) && $params['user'] != '' )
        {
            if ( ! is_array($params['user']) )
            {
                $params['user'] = array($params['user']);
            }
            $url .= '/user/' . implode(',', $params['user']);
        }

        $result = $this->get($url);
        $dom = new DOMDocument();
        $dom->loadXml($result);
        $thingXml = $dom->getElementsByTagName('thing');
        $things = array();
        foreach ($thingXml as $thing)
        {
            $thingdata = $this->parseThingXml($thing);
            if ( is_array($thingdata) )
            {
                $things[] = $thingdata;
            }
        }
        return $things;
	}

	private function parseThingXml($xml)
	{
		$thingdata = array();
		foreach ($xml->childNodes as $i)
		{
			if ( $i->nodeName == 'user' && $i->childNodes->length > 0 )
			{
				$thingdata[$i->nodeName] = $this->loadArrayFromXml($i);
			}
			else if ( $i->nodeName == 'category' && $i->childNodes->length > 0 )
			{
				$thingdata[$i->nodeName] = $this->loadArrayFromXml($i);
			}
			else if ( $i->nodeName == 'tags' && $i->childNodes->length > 0 )
			{
				$thingdata[$i->nodeName] = $this->loadArrayFromXml($i, 'csv');
			}
			else
			{
				$thingdata[$i->nodeName] = trim($i->nodeValue);
			}
		}
		return $thingdata;
	}

	public function getThing($id)
	{
		$result = $this->get($this->actionUrl('/thing/get/id/' . $id));
		if ( $this->http_code == 200 )
		{
			$dom = new DOMDocument();
			$dom->loadXml($result);
			$thingXml = $dom->getElementsByTagName('thing');
			$thing = $this->parseThingXml($thingXml->item(0));
			return $thing;
		}
		else
		{
			return false;
		}
	}

	public function getThingByUrl($url)
	{
		$result = $this->get($this->actionUrl('/thing/get/'), array('url' => urlencode($url)));
		if ( $this->http_code == 200 )
		{
			$dom = new DOMDocument();
			$dom->loadXml($result);
			$thingXml = $dom->getElementsByTagName('thing');
			$thing = $this->parseThingXml($thingXml->item(0));
			return $thing;
		}
		else
		{
			return false;
		}
	}
	
	public function clickThing($id)
	{
		$result = $this->get($this->actionUrl('/thing/click/id/' . $id));
		if ( $this->http_code == 200 )
		{
			return true;
		}
		else
		{
			echo "Click error " . $this->http_code . ', ' . $this->http_info . "<br />";
			return false;
		}
	}

	public function getThingList($userid = null)
	{
		$result = $this->get($this->actionUrl('/thing/listbyuser/id/' . $userid));
		$dom = new DOMDocument();
		$dom->loadXml($result);
		$thingXml = $dom->getElementsByTagName('thing');
		$things = array();
		foreach ($thingXml as $thing)
		{
			$thingdata = $this->parseThingXml($thing);
			if ( is_array($thingdata) )
			{
				$things[] = $thingdata;
			}
		}
		return $things;
	}

	public function getSearchThingList($query)
	{
		$result = $this->get($this->actionUrl('/thing/search/q/' . urlencode($query)) );
		if (empty($result))
		{
			return array();
		}
		$dom = new DOMDocument();
		$dom->loadXml($result);
		$thingXml = $dom->getElementsByTagName('thing');
		$things = array();
		foreach ($thingXml as $thing)
		{
			$thingdata = $this->parseThingXml($thing);
			if ( is_array($thingdata) )
			{
				$things[] = $thingdata;
			}
		}
		return $things;
	}

	public function getLanguages()
	{
		$result = $this->get($this->actionUrl('/feed/languages'));
		$dom = new DOMDocument();
		$dom->loadXml($result);
		$langXml = $dom->getElementsByTagName('language');
		$languages = array();
		$langdata = array();
		foreach ($langXml as $lang)
		{
			foreach ($lang->childNodes as $i)
			{
				$langdata[$i->nodeName] = $i->nodeValue;
			}
			$languages[] = $langdata;
		}
		return $languages;
	}
	
	public function getCategories()
	{
		$result = $this->get($this->actionUrl('/feed/categories'));
		$dom = new DOMDocument();
		$dom->loadXml($result);
		$catXml = $dom->getElementsByTagName('category');
		$categories = array();
		$catdata = array();
		foreach ($catXml as $cat)
		{
			foreach ($cat->childNodes as $i)
			{
				$catdata[$i->nodeName] = $i->nodeValue;
			}
			$categories[] = $catdata;
		}
		return $categories;
	}
	
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
		$userdata = array();
		foreach ($userXml as $user)
		{
			foreach ($user->childNodes as $i)
			{
				$userdata[$i->nodeName] = $i->nodeValue;
			}
		}
		return $userdata;
	}

	private function loadArrayFromXml($node, $type = 'assoc')
	{
		$data = '';
		if ( $type == 'assoc' )
		{
			$data = array();
		}

		foreach ( $node->childNodes as $i )
		{
			if ( $type == 'assoc' )
			{
				$data[$i->nodeName] = trim($i->nodeValue);
			}
			else if ( $type == 'csv' )
			{
				$data .= (  $data != ''  ? ',' : '') .  $i->nodeValue;
			}
		}

		return $data;
	}

	public function getAuthorizeUrl($token, $access = 'read')
	{
		return $this->authorizeUrl() . '?oauth_token=' . $token['oauth_token'] . '&access_scope=' . $access;
	}

	public function getConnectUrl($token, $access = 'read')
	{
		return $this->connectUrl() . '?oauth_token=' . $token['oauth_token'] . '&access_scope=' . $access;
	}

	public function __construct($consumer_key, $consumer_secret, $oauth_token = null, $oauth_token_secret = null)
	{
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
	
	public function getRequestToken($callback = null)
	{
		$parameters = array();

		if ( !empty($callback) )
		{
			$parameters['oauth_callback'] = $callback;
		}

		$request = $this->oAuthRequest($this->requestTokenUrl(), 'GET', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		if ( isset($token['oauth_token']) && isset($token['oauth_token_secret']) )
		{
			$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
			return $token;
		}
		else
		{
			$params = $this->parseParams($request);
			if ( isset($params['oauth_problem']) )
			{
				$this->error = $params['oauth_problem'];
			}
		}
	}

	private function parseParams($header)
	{
		$return = array();
		$params = explode('&', $header);
		foreach ( $params as $param )
		{
			list($key, $value) = explode('=', $param);
			$return[$key] = $value;
		}
		return $return;
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
  	
	public function submitThing($url, $title, $category, $description, $tags, $language, $hidden = false, $temporary = false)
	{
		$dom = new DOMDocument('1.0', 'utf-8');
		
		$doc = $dom->appendChild($dom->createElement('thing'));
		$doc->appendChild(self::xmlAddElement($dom, 'url', $url));
		$doc->appendChild($this->xmlAddElement($dom, 'title', $title));
		$doc->appendChild($this->xmlAddElement($dom, 'category', $category));
		$doc->appendChild($this->xmlAddCdataElement($dom, 'description', $description));
		$doc->appendChild($this->xmlAddElement($dom, 'language', $language));
		$doc->appendChild($this->xmlAddElement($dom, 'hidden', $hidden));
		$doc->appendChild($this->xmlAddElement($dom, 'temporary', $temporary));
		
		$tagsXml = $doc->appendChild($dom->createElement('tags'));

		foreach ( explode(',', $tags) as $tag )
		{
			$tagsXml->appendChild($this->xmlAddElement($dom, 'tag', trim($tag)));
		}
		
		$result = $this->post($this->actionUrl('/thing/register'), array('data' => $dom->saveXml()));
		$dom = new DOMDocument();
		$dom->loadXml($result);
		$thingXml = $dom->getElementsByTagName('thing');
		$thing = $this->parseThingXml($thingXml->item(0));
		
		return $thing;
	}
	
	private function xmlAddElement($dom, $name, $value)
	{
       $element = $dom->createElement($name);
       $element->appendChild($dom->createTextNode($value));
       
       return $element;
	}
			
    private function xmlAddCdataElement($dom, $name, $value)
    {
        $element = $dom->createElement($name);
        $cdata = $element->ownerDocument->createCDATASection($value);
        $element->appendChild($cdata);
        return $element;
    }
}
