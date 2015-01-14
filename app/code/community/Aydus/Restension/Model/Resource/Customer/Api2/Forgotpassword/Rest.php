<?php

/**
 * Forgot password resource implementation
 *
 * @category   Aydus
 * @package    Aydus_Restension
 * @author     Aydus Consulting <davidt@aydus.com>
 */

abstract class Aydus_Restension_Model_Resource_Customer_Api2_Forgotpassword_Rest 
    extends Aydus_Restension_Model_Resource_Customer_Api2_Forgotpassword
{
    /**
     * 
     * @return array $return
     */
    protected function _retrieve() 
    {        
        $return = array();
        
        try {
            
            $request = $this->getRequest();
            
            $email = urldecode($request->getParam('email'));
            
            if (Zend_Validate::is($email, 'EmailAddress')) {
                
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(1)//@todo 
                    ->loadByEmail($email);

                if ($customer->getId()) {
                    
                    $newResetPasswordLinkToken =  Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                } 

            } else {
                
                $this->_critical('Invalid email address', 400);
            }            
            
        } catch (Exception $e) {
            
            $this->_critical($e->getMessage(), 400);
        }
        
        $customerHelper = Mage::helper('customer');
        $return['result'] = $customerHelper->__('If there is an account associated with %s you will receive an email with a link to reset your password.', $customerHelper->escapeHtml($email));
        
        return $return;
    }

}
