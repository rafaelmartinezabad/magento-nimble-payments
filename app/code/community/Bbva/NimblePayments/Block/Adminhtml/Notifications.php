<?php
 
class Bbva_NimblePayments_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    public function getMessage()
    {
        $message= array();
        
        if(Mage::getStoreConfig('payment/nimblepayments_checkout/active')==0){
            $message['message'] = 'Activa el plugins de Nimble Payments by BBVA.';
            $message['type'] = 'plugins';
        }
        else if( is_null(Mage::getStoreConfig('payment/nimblepayments_checkout/token')) ){
            $message['message'] = 'No estás autorizado en Nimble Payments.';
            $message['type'] = 'token';
        }
        return $message;
    }
}