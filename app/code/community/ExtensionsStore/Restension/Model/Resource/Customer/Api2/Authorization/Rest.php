<?php

/**
 * Authorization resource implementation
 * 
 * This guest resource lets you request the Step 2 oauth_token and oauth_verifier 
 * without displaying an authorization web page to the user.
 * 
 * Endpoint format: api/rest/customers/authorization/REQUEST_TOKEN/EMAIL/PASSWORD?oauth_token=REQUEST_TOKEN
 * 
 * The request token is the oauth_token you received in Step 1. Encode the request 
 * token, email and password.
 * 
 * Example: http://www.example.com/api/rest/customers/authorization/f49b2a9681032433c805f5b8628d4c7f/johndoe@gmail.com/testing123?oauth_token=f49b2a9681032433c805f5b8628d4c7f
 * 
 * Returned json example: 
 * 
 * {
 *    oauth_token: "24ee04f5dc13177ec1b00b5e0df86e56"
 *    oauth_verifier: "074fd44bc9bc719c9dfb80cf4dd210a8"
 * }
 * 
 * This token and verifier can be used to request an access token in Step 3.
 *
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Restension
 * @author     Extensions Store <www.extensions-store.com>
 */

abstract class ExtensionsStore_Restension_Model_Resource_Customer_Api2_Authorization_Rest extends ExtensionsStore_Restension_Model_Resource_Customer_Api2_Authorization {

    /**
     * 
     * @param string oauth_token The request token you received in Step 1
     * @return array $authorization 
     */
    protected function _retrieve() 
    {
        $authorization = array('oauth_token' => null, 'oauth_verifier' => null);
        
        $request = $this->getRequest();
        $oauthToken = urldecode($request->getParam('oauth_token'));//also a get variable, required by oauth server
        $email = urldecode($request->getParam('email'));
        $password = urldecode($request->getParam('password'));

        $session = Mage::getSingleton('customer/session');

        try {
        	
        	$websiteId = Mage::app()->getStore()->getWebsite()->getId();
        	$oauthTokenModel = Mage::getModel('oauth/token');
        	$oauthTokenModel->load($oauthToken, 'token');
        	 
        	if ($oauthTokenModel && $oauthTokenModel->getId()){
        		$consumerId = $oauthTokenModel->getConsumerId();
        		$storeId = Mage::helper('extensions_store_restension')->getConsumerStoreId($consumerId);
        		$store = Mage::getModel('core/store')->load($storeId);
        		$website = $store->getWebsite();
        		$websiteId = $website->getId();
        	}
            
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($websiteId);

            if ($customer->authenticate($email, $password)) {
                $session->setCustomerAsLoggedIn($customer);
                $session->renewSession();
            }

            $server = Mage::getModel('oauth/server');
            
            $token = $server->authorizeToken($session->getCustomerId(), Mage_Oauth_Model_Token::USER_TYPE_CUSTOMER);
            
            $authorization['oauth_token'] = $token->getToken();
            $authorization['oauth_verifier'] = $token->getVerifier();
            
        } catch (Exception $e) {

            $this->_critical($e->getMessage());
        }

        return $authorization;
    }

}
