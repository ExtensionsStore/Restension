<?php

/**
 * @category   ExtensionsStore
 * @package    ExtensionsStore_Restension
 * @author     Extensions Store <www.extensions-store.com>
 */

abstract class ExtensionsStore_Restension_Model_Resource_Admin_Api2_User_Rest 
    extends    ExtensionsStore_Restension_Model_Resource_Admin_Api2_User
{

    /**
     * Retrieve admin data
     * 
     * @return array
     */
    protected function _retrieve() 
    {
        $request = $this->getRequest();
        $username = urldecode($request->getParam('username'));
        $apiUser = $this->getApiUser();
    	$userId = $apiUser->getUserId();
    	$admin = Mage::getModel('admin/user')->load($userId);
    	
    	try {
    		if ($admin && $admin->getId() && $admin->getUsername()==$username){
    		
    			$data = $admin->getData();
    		
    		} else {
    			
        	    Mage::throwException(Mage::helper('restension')->__('Invalid User Name or Password.'));
    		}
    		     		
    	} catch(Exception $e){
    		$this->_critical($e->getMessage());
    	}

        return $data;
    }
    
}