<?php

class Bbva_NimblePayments_Model_Notification extends Varien_object
{

    public function getMessage()
    {
        $message= array();
        $checkout = Mage::getModel('nimblepayments/checkout');
        
        if (!$checkout->is3leggedToken()) {
            if($checkout->validCredentials()) {
                $message['message'] = Mage::helper('core')->__('You are not authorized in Nimble Payments.');
                $message['type'] = 'token';
            } else {
                $message['message'] = Mage::helper('core')->__('Activate plugin Nimble Payments by BBVA.');
                $message['type'] = 'plugins';
            }
        }

        return $message;
    }

}
