<?php

/**
 * Consumer test
 *
 * @category    Aydus
 * @package     Aydus_Restension
 * @author      Aydus <davidt@aydus.com>
 */

include('bootstrap.php');

class ConsumerTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        
    }

    public function testSetup() {
    	
        //test app created
        $consumer = Mage::getModel('oauth/consumer');
        $consumer->load('Aydus Restension', 'name');

        $testConsumerCreated = ($consumer->getId()) ? true : false;

        $this->assertTrue($testConsumerCreated);

        //test test customer created
        $websiteId = Mage::app()->getWebsite()->getId();
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail('davidt@aydus.com');

        $testCustomerCreated = ($customer->getId()) ? true : false;

        $this->assertTrue($testCustomerCreated);
    }

}
