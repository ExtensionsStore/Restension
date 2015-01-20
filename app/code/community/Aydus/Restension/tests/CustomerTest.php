<?php

/**
 * Extension test
 *
 * @category    Aydus
 * @package     Aydus_Restension
 * @author      Aydus <davidt@aydus.com>
 */
include('bootstrap.php');

class CustomerTest extends PHPUnit_Framework_TestCase {

	protected $_consumer;
    protected $_key;
    protected $_secret;
    protected $_callbackUrl;

    public function setUp() {
    	
        $consumer = Mage::getModel('oauth/consumer');
        $consumer->load('Aydus Restension', 'name');
        
        $this->_consumer = $consumer;
        $this->_key = $consumer->getKey();
        $this->_secret = $consumer->getSecret();
        $this->_callbackUrl = $consumer->getCallbackUrl();
    }

    protected function _getHeader($endpoint) {
        $header = Mage::helper('aydus_restension')->getOauth1Header($this->_key, $this->_secret, $this->_callbackUrl, $endpoint);

        return $header;
    }
    
    protected function _getRequestToken()
    {
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
    	    	
    	return array($oauth_token, $oauth_token_secret, $oauth_callback_confirmed);
    }

    /**
     * Test login authorization
     */
    public function testAuthorize() {
    	
        //get request token
        list($oauth_token, $oauth_token_secret, $oauth_callback_confirmed) = $this->_getRequestToken();

        $gotToken = (!empty($oauth_token) && !empty($oauth_token_secret) && !empty($oauth_callback_confirmed)) ? true : false;

        $this->assertTrue($gotToken);
        
        //get authorization token
        $authorizationEndpoint = 'api/rest/customers/authorization';
        $authorizationUrl = $this->_callbackUrl . $authorizationEndpoint . '/' . urlencode($oauth_token) . '/' . urlencode('davidt@aydus.com') . '/' . urlencode('testing123') . '?oauth_token=' . urlencode($oauth_token);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $authorizationUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        $authParams = json_decode($response);

        $gotAuthorization = (!empty($authParams->oauth_token) && !empty($authParams->oauth_verifier)) ? true : false;

        $this->assertTrue($gotAuthorization);
    }

    /**
     * Test forgot password
     */
    public function testForgotPassword() {
        //get authorization token
        $endpoint = 'api/rest/customers/forgotpassword';
        $url = $this->_callbackUrl . $endpoint . '/' . urlencode('davidt@aydus.com');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        $params = json_decode($response);

        $gotResult = (!empty($params->result)) ? true : false;

        $this->assertTrue($gotResult);
    }

    /**
     * Test account registration
     */
    public function testAccountRegistration() {
    	
    	list($oauth_token, $oauth_token_secret, $oauth_callback_confirmed) = $this->_getRequestToken();
    	
    	$gotToken = (!empty($oauth_token) && !empty($oauth_token_secret) && !empty($oauth_callback_confirmed)) ? true : false;
    	
    	$this->assertTrue($gotToken);
    	    	
    	$accountEndpoint = 'api/rest/customers/account';
    	$accountUrl = $this->_callbackUrl . $accountEndpoint . '/' . urlencode($oauth_token);
    	    	
    	$email = 'davidt'.rand().'@aydus.com';
    	$storeId = Mage::helper('aydus_restension')->getConsumerStoreId($this->_consumer);
    	$store = Mage::getModel('core/store')->load($storeId);
    	$website = $store->getWebsite();
    	$websiteId = $website->getId();
    	
    	$params = array(
    		'firstname'	=> 'David',
    		'lastname'	=> 'Tay',
    		'email'	=> $email,
    		'password'	=> 'testing123',
    		'confirmation'	=> 'testing123',
    		'store_id'	=> $storeId,
    	);
    	
    	$data = json_encode($params);    
    	 
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
        );
        
    	curl_setopt($ch, CURLOPT_URL, $accountUrl);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	
    	$response = curl_exec($ch);
    	    	
    	$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    	$headerStr = substr($response, 0, $headerSize);
    	$headersAr = Mage::helper('aydus_restension')->getHeadersArray($headerStr);
    	 
    	curl_close($ch);
    	
    	$gotLocation  = false;

    	if (is_array($headersAr) && count($headersAr)>0){
    		
    		$location = '';
    		
    		foreach ($headersAr as $headerAr){
    			
    			foreach ($headerAr as $headerKey=>$headerValue){
    				
    				if ($headerKey == 'Location'){
    					$location = $headerValue;
    					$parsedUrlAr = parse_url($location);
    					
    					if (is_array($parsedUrlAr) && count($parsedUrlAr)>0){
    						    						
    						$gotLocation = true;
    						
    					}
    					
    				}
    				
    			}
    			
    		}
    		    		
    	}
    	
    	$this->assertTrue($gotLocation);
    	 
    	
    	Mage::register('isSecureArea', true);
    	$customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($email);
    	if ($customer->getId()){
    	   $customer->delete();
    	}
    	Mage::unregister('isSecureArea');
    }
    

}
