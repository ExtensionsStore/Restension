<?php

/**
 * Account resource implementation
 * 
 * Register a new customer account without displaying a web page to the user.
 * 
 * Obtain a normal request token (Step 1). 
 * 
 * Endpoint format: api/rest/customers/account/REQUEST_TOKEN
 * 
 * Example: http://www.example.com/api/rest/customers/account/f49b2a9681032433c805f5b8628d4c7f
 * 
 * POST json:
 * 
 * {
 *   "firstname": "John",
 *   "lastname": "Doe",
 *   "email": "john@doe.com",
 *   "password": "testing123",
 *   "confirmation" : "testing123",
 *   "store_id" : 1
 * }
 * 
 * The authorization url location is returned on successful registration
 *
 * @category   Aydus
 * @package    Aydus_Restension
 * @author     Aydus Consulting <davidt@aydus.com>
 */

abstract class Aydus_Restension_Model_Resource_Customer_Api2_Account_Rest 
    extends Aydus_Restension_Model_Resource_Customer_Api2_Account 
{

    /**
     * Register a customer
     * 
     * @return string $authorizationUrl
     */
    protected function _create(array $data) 
    {
        try {
            
            $request = $this->getRequest();
            $oauthToken = urldecode($request->getParam('oauth_token'));
            
            $tokenModel = Mage::getModel('oauth/token')->load($oauthToken, 'token');
            if (!$tokenModel || !$tokenModel->getId() || $tokenModel->getType() != 'request' || $tokenModel->getRevoked() || $tokenModel->getAuthorized()){
                throw new Exception('Invalid token');
            }
            
            $email = $data['email'];
            $password = $data['password'];
            $confirmation = $data['confirmation'];
            $firstname = $data['firstname'];
            $lastname = $data['lastname'];
            $storeId = ($data['store_id']) ? $data['store_id'] : Mage::app()->getStore()->getId();
            $groupId = Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $storeId);

            $customer = Mage::getModel('customer/customer')->setWebsiteId(1);
            $customer->setData('group_id', $groupId);     

            $customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('customer_account_create');
            $customerForm->setEntity($customer);        

            $customerErrors = $customerForm->validateData($data);
            
            if ($customerErrors !== true) {
                
                reset($customerErrors);
                $firstKey = key($customerErrors);                
                $error = $customerErrors[$firstKey];
                throw new Exception($error);

            } else {
                
                $customerForm->compactData($data);
                $customer->setPassword($password);
                $customer->setConfirmation($confirmation);
                $customerErrors = $customer->validate();
                
                if (is_array($customerErrors)) {
                    
                    reset($customerErrors);
                    $firstKey = key($customerErrors);                
                    $error = $customerErrors[$firstKey];
                    throw new Exception($error);
                }
            } 
                
            $customer->save();
            
        } catch (Exception $e) {

            $this->_critical($e->getMessage(), 400);
        }
        
        $oauthToken = urlencode($oauthToken);
        $email = urlencode($email);
        $password = urlencode($password);
        
        $authorizationUrl = Mage::getUrl('api/rest/customers/authorization/'.$oauthToken.'/'.$email.'/'.$password);

        return $authorizationUrl;
    }

}
