<?php

/**
 * Observer
 *
 * @category   Aydus
 * @package    Aydus_Restension
 * @author     Aydus Consulting <davidt@aydus.com>
 */
class Aydus_Restension_Model_Observer 
{

    /**
     * Save the consumer's default store
     * 
     * @param Varien_Event_Observer $observer
     */
    public function saveConsumerStore($observer)
    {
        $object = $observer->getObject();
        
        if (get_class($object)=='Mage_Oauth_Model_Consumer'){
            
            $consumerId = $object->getId();
            $storeId = $object->getStoreId();
            $stores = Mage::app()->getStores();
            
            if ($consumerId && $storeId && in_array($storeId, array_keys($stores))){
                
                $resource = Mage::getSingleton('core/resource');
                $write = $resource->getConnection('core_write');
                $prefix = Mage::getConfig()->getTablePrefix();
                $table = $prefix.'aydus_restension_consumer';
                
                $write->query("REPLACE INTO $table (consumer_id, store_id) VALUES('$consumerId','$storeId')");
            }
            
        }
        
    }
    
}
