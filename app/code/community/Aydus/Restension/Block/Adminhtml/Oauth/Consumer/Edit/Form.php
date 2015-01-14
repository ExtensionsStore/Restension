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
        
        $form = $this->getForm();
        
        $fieldset = $form->getElement('base_fieldset');
        
        $fieldset->addField('some_field', 'text', array(
                'name' => 'some_field',
                'label' => Mage::helper('cms')->__('Some Field'),
                'title' => Mage::helper('cms')->__('Some Field')
            )
        );
        
        return $this;
    }
}
