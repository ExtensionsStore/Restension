<?php

/**
 * Authorization resource
 *
 * @category   Aydus
 * @package    Aydus_Restension
 * @author     Aydus Consulting <davidt@aydus.com>
 */
abstract class Aydus_Restension_Model_Customer_Api2_Authorization_Rest extends Aydus_Restension_Model_Customer_Api2_Authorization {

    /**
     * Retrieve the authorization (step 2) oauth_token and oauth_verifier
     * 
     * http://www.example.com/api/rest/customers/authorization/f49b2a9681032433c805f5b8628d4c7f/johndoe@gmail.com/testing123?oauth_token=f49b2a9681032433c805f5b8628d4c7f
     * 
     * @param string oauth_token The request token
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
            
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(1);//@todo select website

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
