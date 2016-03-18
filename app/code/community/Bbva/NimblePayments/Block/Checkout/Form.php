<?php

class Bbva_NimblePayments_Block_Checkout_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {        
        parent::_construct();
        $this->setTemplate('nimblepayments/checkout/form.phtml');
    }
}
