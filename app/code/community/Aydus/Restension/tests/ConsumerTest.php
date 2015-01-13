<?php

/**
 * Setup test
 *
 * @category    Aydus
 * @package     Aydus_Restension
 * @author      Aydus <davidt@aydus.com>
 */

include('bootstrap.php');

class ConsumerTest extends PHPUnit_Framework_TestCase 
{
	public function setUp()	
	{
		
	}
	
	public function testSetup()
	{
		//test app created
		$consumer = Mage::getModel('oauth/consumer');
		$consumer->load('Aydus Restension', 'name');
		
		$testConsumerCreated = ($consumer->getId()) ? true : false; 
		
		$this->assertTrue($testConsumerCreated);
		
		$websiteId = Mage::app()->getWebsite()->getId();
		$customer = Mage::getModel("customer/customer");
		$customer->setWebsiteId($websiteId);
		$customer->loadByEmail('davidt@aydus.com');
		
		$testCustomerCreated = ($customer->getId()) ? true : false;
		
		$this->assertTrue($testCustomerCreated);
	}
	
	protected function _getAuthorizationHeader($endpoint)
	{
		$consumer = Mage::getModel('oauth/consumer');
		$consumer->load('Aydus Restension', 'name');
		$key = $consumer->getKey();
		$secret = $consumer->getSecret();
		$callbackUrl = $consumer->getCallbackUrl();
		
		$realm = $callbackUrl;
		$endpointUrl = $realm.$endpoint;
		$oauthCallback = $callbackUrl;
		$oauthConsumerKey = $key;
		$oauthConsumerSecret = $secret;
		$oauthNonce = substr(md5(uniqid('oauth_nonce_', true)),0,16);
		$oauthSignatureMethod = "HMAC-SHA1";
		$oauthTimestamp = time();
		$oauthVersion = "1.0";
		$oauthMethod = "POST";
		
		$params = array(
		        "oauth_callback" => $oauthCallback,
		        "oauth_consumer_key" => $oauthConsumerKey,
		        "oauth_nonce" => $oauthNonce,
		        "oauth_signature_method" => $oauthSignatureMethod,
		        "oauth_timestamp" => $oauthTimestamp,
		        "oauth_version" => $oauthVersion,
		);
		
		$data = http_build_query($params);
		
		$encodedData = $oauthMethod."&".urlencode($endpointUrl)."&".urlencode($data);
		$key = $oauthConsumerSecret."&";
		$signature = hash_hmac("sha1", $encodedData, $key, 1);
		$oauthSignature = base64_encode($signature);
		
		$header = "Authorization: OAuth realm=\"$realm\",";
		foreach ($params as $key=>$value){
		    $header .=  $key.'="'.$value."\",";
		}
		$header .= "oauth_signature=\"".$oauthSignature.'"';
		
		return $header;
	}
	
	public function testAuthorize()
	{
		$consumer = Mage::getModel('oauth/consumer');
		$consumer->load('Aydus Restension', 'name');
		$callbackUrl = $consumer->getCallbackUrl();
		
		//get request token
		$endpoint = "oauth/initiate";
		$header = $this->_getAuthorizationHeader($endpoint);
		$endpointUrl = $callbackUrl.$endpoint;		
				
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
		curl_setopt($ch, CURLOPT_URL, $endpointUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$response = curl_exec($ch);
		
		parse_str($response);
		        		
		$gotToken = (!empty($oauth_token) && !empty($oauth_token_secret) && !empty($oauth_callback_confirmed)) ? true : false;
		
		$this->assertTrue($gotToken);
		
        //get authorization token
		//http://www.example.com/api/rest/customers/authorization/f49b2a9681032433c805f5b8628d4c7f/johndoe@gmail.com/testing123?oauth_token=f49b2a9681032433c805f5b8628d4c7f        
		$authorizationEndpoint = 'api/rest/customers/authorization';
		$header = $this->_getAuthorizationHeader($authorizationEndpoint);
		$authorizationUrl = $callbackUrl.$authorizationEndpoint.'/'.$oauth_token.'/davidt@aydus.com/testing123?oauth_token='.$oauth_token;
		
		var_dump($authorizationUrl);
		
		//curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
		curl_setopt($ch, CURLOPT_URL, $authorizationUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$response = curl_exec($ch);
		
		print_r($response);
        
		
		curl_close($ch);
	}
		
}
