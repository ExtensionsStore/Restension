<?php

/**
 * Abstract API2 class for rate instance
 *
 * @category   Aydus
 * @package    Aydus_Restension
 * @author     Aydus Consulting <davidt@aydus.com>
 */

abstract class Aydus_Restension_Model_Resource_Shipping_Api2_Rate_Rest extends Aydus_Restension_Model_Resource_Shipping_Api2_Rate
{
    protected function _create(array $data) 
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');  
        $write = $resource->getConnection('core_write'); 
        $prefix = Mage::getConfig()->getTablePrefix();
        $table = $prefix.'aydus_restension_shippingmethods';
        
        $key = json_encode($data);
        $hash = md5($key);
        $date = date("Y-m-d H:i:s");
        
        $write->query("DELETE FROM $table WHERE date_created < DATE_SUB('$date', INTERVAL 1 DAY)");
        
        $json = $read->fetchOne("SELECT json FROM $table WHERE hash = '$hash' AND date_created >= CURDATE()");

        if (!$json){
            
            $storeId = Mage::app()->getStore()->getId();

            //create request
            $request = Mage::getModel('shipping/rate_request');
            $request->setStoreId($storeId);

            //check country
            $country = $data['country'];

            $countryDirectory = Mage::getModel('directory/country');
            $countryModel = $countryDirectory->loadByCode($country);
            $countries = Mage::getModel('directory/country')->getResourceCollection()->loadByStore($storeId);
            $countryIds = $countries->getAllIds();

            if ($countryModel->getId() && in_array($countryModel->getId(), $countryIds)){

                    $request->setDestCountryId($countryModel->getId());

            } else {

                    $this->_critical('Invalid country, please specify ISO2 code in param country',400);
            }

            $countryId = $countryModel->getId();

            //check region
            $region = $data['region'];
            $regionCode = $region;
            $regionDirectory = Mage::getModel('directory/region');
            $regionModel = $regionDirectory->loadByCode($regionCode, $countryId);

            if ($regionModel->getId()){

                    $regionId =$regionModel->getId();
                    $request->setDestRegionId($regionId);

            } else {

                    $regionName = $region;
                    $regionModel = $regionDirectory->loadByName($regionName, $countryId);

                    if ($regionModel->getId()){
                            $regionId = $regionModel->getId();
                            $request->setDestRegionId($regionId);
                    } else {
                            $this->_critical('Invalid region',400);
                    }
            }

            $request->setDestStreet($data['street'][0]);
            $request->setDestCity($data['city']);
            $request->setDestPostcode($data['postcode']);

            //get package weight
            $packageWeight = 0;
            $items = $this->getRequest()->getParam('items');
            $filter = new Zend_Filter_LocalizedToNormalized(
                            array('locale' => Mage::app()->getLocale()->getLocaleCode())
            );

            if (count($items)>0){
                    foreach ($items as $item){

                            $productId = $item['product'];
                            $qty = $filter->filter($item['qty']);

                            $product = Mage::getModel('catalog/product')
                            ->setStoreId($storeId)
                            ->load($productId);

                            $packageWeight += $product->getWeight() * $qty;
                    }
            }

            $request->setPackageWeight($packageWeight);

            //@todo  package logic
            $request->setPackageQty(1);

            //get rates for available carriers
            $result = Mage::getModel('shipping/shipping')->collectRates($request)->getResult();

            $rates = array('rates'=>array());

            foreach ($result->getAllRates() as $rate){

                    $rates['rates'][] = array(
                            "carrier" => $rate->getCarrier(),
                            "carrier_title" => $rate->getCarrierTitle(),
                            "code" => $rate->getMethod(),
                            "price" => $rate->getPrice(),
                            "title" => $rate->getMethodTitle(),
                    );
            }     
            
            $json = json_encode($rates);
            $datetime = date("Y-m-d H:i:s");
            
            $write->query("INSERT INTO $table VALUES('$hash','$json','$datetime')");
        }
        
        $resource = new Varien_Object();
        $resource->setId($hash);
        
        $shippingMethodsLocation = $this->_getLocation($resource);
        
        return $shippingMethodsLocation;
    }    
    
    
    /**
     * Retrieve list of shipping rates
     *
     * @return array
     */
    protected function _retrieve()
    {
        $request = $this->getRequest();
        $hash = urldecode($request->getParam('id'));
        $shippingMethods = array("rates"=>array());
        
        if ($hash){
            
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');  
            $prefix = Mage::getConfig()->getTablePrefix();
            $table = $prefix.'wit_restension_shippingmethods';

            $json = $read->fetchOne("SELECT json FROM $table WHERE hash = '$hash' AND date_created >= CURDATE()");
            $shippingMethods = json_decode($json, true);
        }
        
        return $shippingMethods;
    }

}
