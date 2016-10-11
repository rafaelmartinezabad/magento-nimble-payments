<?php

class Bbva_NimblePayments_Model_Notification extends Varien_object
{

    public function getMessage()
    {
        $message= array();

        if(Mage::getStoreConfig('payment/nimblepayments_checkout/active')==0){
            $message['message'] = Mage::helper('core')->__('Activate plugin Nimble Payments by BBVA.');
            $message['type'] = 'plugins';
        } else if(!Mage::getModel('nimblepayments/checkout')->showAuthorize()){
            $message['message'] = Mage::helper('core')->__('You are not authorized in Nimble Payments.');
            $message['type'] = 'token';
        }

        return $message;
    }

}
