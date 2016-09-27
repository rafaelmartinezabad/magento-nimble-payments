<?php
 
class Bbva_NimblePayments_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    public function getMessage()
    {
        $message= array();
        
        if(Mage::getStoreConfig('payment/nimblepayments_checkout/active')==0){
            $message['message'] = $this->__('Activate plugin Nimble Payments by BBVA.');
            $message['type'] = 'plugins';
        }
        else if( empty(Mage::getStoreConfig('payment/nimblepayments_checkout/token'))){
            $message['message'] = $this->__('You are not authorized in Nimble Payments.');
            $message['type'] = 'token';
        }
        return $message;
    }
}