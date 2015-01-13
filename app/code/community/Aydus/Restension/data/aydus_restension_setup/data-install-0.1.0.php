<?php

/**
 * Install Restention test data
 *
 * @category   Aydus
 * @package    Aydus_Restension
 * @author     Aydus Consulting <davidt@aydus.com>
 */

$installer = $this;
$installer->startSetup();

$consumer = Mage::getModel('oauth/consumer');
$consumer->load('Aydus Restension', 'name');

if (!$consumer->getId()){
	
	try {
		
		$helper = Mage::helper('oauth');
		
		$data = array(
		        'name' => 'Aydus Restension',
		        'key'  => $helper->generateConsumerKey(),
		        'secret' => $helper->generateConsumerSecret(),
		        'callback_url' => 'http://'.$_SERVER['HTTP_HOST'].'/',
		        'rejected_callback_url' => '',
		);
		
		$consumer->addData($data);
		$consumer->save();
		
		
		
	}catch (Exception $e){
		
		Mage::log($e->getMessage(),null, 'aydus_restension.log');
	}

}

$websiteId = Mage::app()->getWebsite()->getId();
$customer = Mage::getModel("customer/customer");
$customer->setWebsiteId($websiteId);
$customer->loadByEmail('davidt@aydus.com');

if (!$customer->getId()){
	    
	$customer
    	->setFirstname('Aydus')
    	->setLastname('Restension')
    	->setEmail('davidt@aydus.com')
    	->setPassword('testing123');
	
	try{
	    $customer->save();
	}
	catch (Exception $e) {
	    Mage::log($e->getMessage(),null, 'aydus_restension.log');
	}	
}

$installer->endSetup();
