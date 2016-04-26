<?php

require_once Mage::getBaseDir() . '/app/code/community/Bbva/NimblePayments/Model/StoredCard.php';
 
class Bbva_NimblePayments_Block_Checkout_Form extends Mage_Payment_Block_Form
{
    protected function _construct() {
        
        parent::_construct();
        $this->setTemplate('nimblepayments/checkout/form.phtml');
    }
    
    protected function _prepareLayout() {
            
        $this->setData('storedcards', Mage::getSingleton('Bbva_NimblePayments_Model_StoredCard')->getListStoredCards());
        return parent::_prepareLayout();
    }
}
