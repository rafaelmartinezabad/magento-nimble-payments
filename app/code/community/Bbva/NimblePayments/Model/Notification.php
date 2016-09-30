<?php

class Bbva_NimblePayments_Model_Notification extends Varien_object
{

    public function getMessage()
    {
        $message= array();

        if(Mage::getStoreConfig('payment/nimblepayments_checkout/active')==0){
            $message['message'] = Mage::helper('core')->__('Activate plugin Nimble Payments by BBVA.');
            $message['type'] = 'plugins';
        } else if(!$this->is3leggedToken()){
            $message['message'] = Mage::helper('core')->__('You are not authorized in Nimble Payments.');
            $message['type'] = 'token';
        }

        return $message;
    }

    private function is3leggedToken() {
        $checkout = Mage::getModel('nimblepayments/checkout');
        $valid_token = empty($checkout->getToken()) ? false : true;
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIAccount.php';
        
        try {
            $params = array(
                'clientId' => $checkout->getMerchantId(),
                'clientSecret' => $checkout->getSecretKey(),
                'token' => $checkout->getToken(),
                'mode' => NimbleAPIConfig::MODE
            );
            $nimble_api = new NimbleAPI($params);
            $summary = NimbleAPIAccount::balanceSummary($nimble_api);
            
            if ( !isset($summary['result']) || ! isset($summary['result']['code']) || 200 != $summary['result']['code'] || !isset($summary['data'])){
                $valid_token = false;
            }

        } catch (Exception $e) {
            $valid_token = false;
        }

        return $valid_token;
    }

}
