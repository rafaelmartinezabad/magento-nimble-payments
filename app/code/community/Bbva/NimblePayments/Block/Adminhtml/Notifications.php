<?php
 
class Bbva_NimblePayments_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    public function getMessage()
    {
        $message= array();
        $vc = Mage::getModel('nimblepayments/checkout')->isValidCredentials();
        
        if(Mage::getStoreConfig('payment/nimblepayments_checkout/active')==0 || ! $vc){
            $message['message'] = $this->__('Activate plugin Nimble Payments by BBVA.');
            $message['type'] = 'plugins';
        }
        else if( empty(Mage::getStoreConfig('payment/nimblepayments_checkout/token')) || !$this->is3leggedtoken()){
            $message['message'] = $this->__('You are not authorized in Nimble Payments.');
            $message['type'] = 'token';
        }
        return $message;
    }

    private function is3leggedtoken()
    {
        $is3leggedtoken = true;

        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIAccount.php';

        try {
            $params = array(
                'clientId' => Mage::getStoreConfig('payment/nimblepayments_checkout/merchant_id'),
                'clientSecret' => Mage::getStoreConfig('payment/nimblepayments_checkout/secret_key'),
                'token' => Mage::getStoreConfig('payment/nimblepayments_checkout/token'),
                'mode' => NimbleAPIConfig::MODE
            );
            $nimble_api = new NimbleAPI($params);
            $summary = NimbleAPIAccount::balanceSummary($nimble_api);

            if ( !isset($summary['result']) || ! isset($summary['result']['code']) || 200 != $summary['result']['code'] || !isset($summary['data'])){
                $is3leggedtoken = false;
            }

        } catch (Exception $e) {
            $is3leggedtoken = false;
        }

        return $is3leggedtoken;
    }
}