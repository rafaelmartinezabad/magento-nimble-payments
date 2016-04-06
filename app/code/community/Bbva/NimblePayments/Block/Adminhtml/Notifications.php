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
        else if($this->getTokenValidate() == false){
            $message['message'] = 'No estás autorizado en Nimble Payments.';
            $message['type'] = 'token';
        }
        return $message;
    }
    
    function getTokenValidate(){
        
    require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
    require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
    require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIReport.php';
    
    
    $token=false;
        
    if(Mage::getStoreConfig('payment/nimblepayments_checkout/token')){
        try {
            $params = array(
                'clientId' => Mage::getStoreConfig('payment/nimblepayments_checkout/merchant_id'),
                'clientSecret' => Mage::getStoreConfig('payment/nimblepayments_checkout/secret_key'),
                'token' => Mage::getStoreConfig('payment/nimblepayments_checkout/token'),
                'mode' => NimbleAPIConfig::MODE
            );
            $nimble_api = new NimbleAPI($params);
            $commerces = NimbleAPIReport::getCommerces($nimble_api, 'enabled');
            if (!isset($commerces['error'])){
                $token=true;
            } else {
                $token=false;
            }

        } catch (Exception $e) {
            $token=false;
        }       

     }
        
     return $token;
     }
}