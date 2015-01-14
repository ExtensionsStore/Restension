<?php

/**
 * Extension test
 *
 * @category    Aydus
 * @package     Aydus_Restension
 * @author      Aydus <davidt@aydus.com>
 */

include('bootstrap.php');

class CustomerTest extends PHPUnit_Framework_TestCase 
{
	protected $_key;
	protected $_secret;
	protected $_callbackUrl;
	
	public function setUp()	
	{
		$consumer = Mage::getModel('oauth/consumer');
		$consumer->load('Aydus Restension', 'name');
		$this->_key = $consumer->getKey();
		$this->_secret = $consumer->getSecret();
		$this->_callbackUrl = $consumer->getCallbackUrl();
	}
	
    protected function _getHeader($endpoint) 
    {
        $header = Mage::helper('aydus_restension')->getOauth1Header($this->_key, $this->_secret, $this->_callbackUrl, $endpoint);

        return $header;
    }
    
    public function testAuthorize() 
    {
        $consumer = Mage::getModel('oauth/consumer');
        $consumer->load('Aydus Restension', 'name');

        //get request token
        $endpoint = "oauth/initiate";
        $header = $this->_getHeader($endpoint);
        $endpointUrl = $this->_callbackUrl . $endpoint;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
        curl_setopt($ch, CURLOPT_URL, $endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        
        curl_close($ch);
        
        parse_str($response);

        $gotToken = (!empty($oauth_token) && !empty($oauth_token_secret) && !empty($oauth_callback_confirmed)) ? true : false;

        $this->assertTrue($gotToken);

        //get authorization token
        $authorizationEndpoint = 'api/rest/customers/authorization';
        $authorizationUrl = $this->_callbackUrl . $authorizationEndpoint . '/' . urlencode($oauth_token) . '/'.  urlencode('davidt@aydus.com').'/'.urlencode('testing123').'?oauth_token=' . urlencode($oauth_token);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $authorizationUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        
        curl_close($ch);
        
        $authParams = json_decode($response);
        
        $gotAuthorization = (!empty($authParams->oauth_token) && !empty($authParams->oauth_verifier)) ? true : false;
        
        $this->assertTrue($gotAuthorization);
    }
    
    public function testForgotPassword()
    {
    	//get authorization token
    	$endpoint = 'api/rest/customers/forgotpassword';
    	$url = $this->_callbackUrl . $endpoint . '/'.  urlencode('davidt@aydus.com');
    	
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	
    	$response = curl_exec($ch);
    	
    	curl_close($ch);
    	
    	$params = json_decode($response);
    	    	
    	$gotResult = (!empty($params->result)) ? true : false;
    	
    	$this->assertTrue($gotResult);    	
    }
    
    public function testAccountRegistration()
    {
    	
    }
		
}
