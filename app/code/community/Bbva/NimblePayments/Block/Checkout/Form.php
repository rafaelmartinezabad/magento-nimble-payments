<?php

class Bbva_NimblePayments_Block_Checkout_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {        
        parent::_construct();
        $this->setTemplate('nimblepayments/checkout/form.phtml');
    }
    
    protected function _prepareLayout() {
        $customerhascards = false;
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customerData->getId();
            $this->setData('customerid', $customerId);
            
            //TODO Call NimbleAPI storedcard
            $cards = array(
                '1' => array( 'CardPan' => '2016', 'CardType' => 'VISA'),
                '2' => array( 'CardPan' => '8880', 'CardType' => 'MASTERCARD')
            );
            $this->setData('storedcard', $cards);
            $customerhascards = true;
        }
        $this->setData('customerhascards', $customerhascards);
        return parent::_prepareLayout();
    }
}
