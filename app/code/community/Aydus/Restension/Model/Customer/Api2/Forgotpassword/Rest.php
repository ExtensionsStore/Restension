<?php

/**
 * Forgot password resource
 *
 * @category   Aydus
 * @package    Aydus_Restension
 * @author     Aydus Consulting <davidt@aydus.com>
 */

abstract class Aydus_Restension_Model_Customer_Api2_Forgotpassword_Rest 
    extends Aydus_Restension_Model_Customer_Api2_Forgotpassword
{
    /**
     * 
     * @return array $result
     */
    protected function _retrieve() 
    {        
        $return = array();
        
        try {
            
            $request = $this->getRequest();
            
            $email = urldecode($request->getParam('email'));
            
            if ($email) {
                
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    throw new Exception('Invalid email address.');
                }

                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(1)//@todo 
                    ->loadByEmail($email);

                if ($customer->getId()) {
                    
                    $newResetPasswordLinkToken =  Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                    
                    
                } else {
                    throw new Exception('Customer does not exist');
                }

            } else {
                
                throw new Exception('Invalid email address.');
            }            
            
        } catch (Exception $e) {
            
            $this->_critical($e->getMessage(), 400);
        }
        
        $customerHelper = Mage::helper('customer');
        $return['result'] = $customerHelper->__('If there is an account associated with %s you will receive an email with a link to reset your password.', $customerHelper->escapeHtml($email));
        
        return $return;
    }

}
