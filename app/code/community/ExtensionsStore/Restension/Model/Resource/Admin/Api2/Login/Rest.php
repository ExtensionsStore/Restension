<?php

/**
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Restension
 * @author     Extensions Store <www.extensions-store.com>
 */

abstract class ExtensionsStore_Restension_Model_Resource_Admin_Api2_Login_Rest 
    extends    ExtensionsStore_Restension_Model_Resource_Admin_Api2_Login
{

    /**
     * Retrieve admin authorization
     * 
     * @return array
     */
    protected function _retrieve() 
    {
        $authorization = array('oauth_token' => null, 'oauth_verifier' => null);
        
        $request = $this->getRequest();
        $oauthToken = urldecode($request->getParam('oauth_token'));//also a get variable, required by oauth server
        $username = urldecode($request->getParam('username'));
        $password = urldecode($request->getParam('password'));
        
        try {
        	
        	$oauthTokenModel = Mage::getModel('oauth/token');
        	$oauthTokenModel->load($oauthToken, 'token');
        	 
        	if ($oauthTokenModel && $oauthTokenModel->getId()){
        	    $user = Mage::getModel('admin/user');
        	    $user->login($username, $password);
        	    if ($user->getId()) {
        	        
        	        $server = Mage::getModel('oauth/server');
        	        $token = $server->authorizeToken($user->getId(), Mage_Oauth_Model_Token::USER_TYPE_ADMIN);
        	         
        	        $authorization['oauth_token'] = $token->getToken();
        	        $authorization['oauth_verifier'] = $token->getVerifier();
        	    
        	    } else {
        	        Mage::throwException(Mage::helper('restension')->__('Invalid User Name or Password.'));
        	    }
        	} else {
        	    Mage::throwException(Mage::helper('restension')->__('Invalid request token.'));
        	}
            
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }
        return $authorization;
    }
    
}