<?php

/**
 * OAuth consumer edit form block
 *
 * @category   Aydus
 * @package    Aydus_Restension
 * @author     Aydus Consulting <davidt@aydus.com>
 */

class Aydus_Restension_Block_Adminhtml_Oauth_Consumer_Edit_Form extends Mage_Oauth_Block_Adminhtml_Oauth_Consumer_Edit_Form
{
    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Oauth_Block_Adminhtml_Oauth_Consumer_Edit_Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        
        $model = $this->getModel();
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $prefix = Mage::getConfig()->getTablePrefix();
        $table = $prefix.'aydus_restension_consumer';
                
        $storeId = '';
        $consumerId = $model->getId();
        if ($consumerId){
            $storeId = $read->fetchOne("SELECT store_id FROM $table WHERE consumer_id = '$consumerId'");        
        }
        
        $form = $this->getForm();
        
        $fieldset = $form->getElement('base_fieldset');
        
        $fieldset->addField('store_id', 'select', array(
                'name' => 'store_id',
                'label' => Mage::helper('aydus_restension')->__('Default Store'),
                'title' => Mage::helper('aydus_restension')->__('Default Store'),
                'values' => Mage::getSingleton('adminhtml/system_store')->setIsAdminScopeAllowed(false)->getStoreValuesForForm(false, true),
                'value' => $storeId,
            )
        );
        
        return $this;
    }
}
