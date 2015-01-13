<?php

/**
 * Register customer resource
 *
 * @category   Aydus
 * @package    Aydus_Restension
 * @author     Aydus Consulting <davidt@aydus.com>
 */

abstract class Aydus_Restension_Model_Customer_Api2_Registration_Rest 
    extends Aydus_Restension_Model_Customer_Api2_Registration 
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
