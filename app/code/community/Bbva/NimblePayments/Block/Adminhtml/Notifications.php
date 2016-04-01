<?php
 
class Bbva_NimblePayments_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    public function getMessage()
    {
        if(Mage::getStoreConfig('payment/nimblepayments_checkout/active')==0){
            $message = 'Activa el plugins de Nimble Payments by BBVA';
            return $message;
        }
    }
}