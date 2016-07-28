<?php
class Bbva_NimblePayments_Block_Dashboard_Summary extends Mage_Adminhtml_Block_Dashboard_Abstract
{
    protected function _construct()
    {
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $invalid_token = $this->getToken() ? false : true;
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
            $summary = $summary = NimbleAPIAccount::balanceSummary($nimble_api);
            
            if ( !isset($summary['result']) || ! isset($summary['result']['code']) || 200 != $summary['result']['code'] || !isset($summary['data'])){
                $invalid_token = true;
            } else {
                $this->setTemplate('nimblepayments/dashboard_widget.phtml');
                $this->summary = $summary;
            }

        } catch (Exception $e) {
            $invalid_token = true;
        }
        
        if ($invalid_token){
            $this->setTemplate('nimblepayments/authorization.phtml');
            $this->url = $this->getOauth3Url();
        }
    }
   
    
    function getOauth3Url(){
        
        $url = '';
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        
        $objBBVA=new Bbva_NimblePayments_Model_Checkout();
        $params=$objBBVA->getParams();

        try {
            $nimble_api = new NimbleAPI($params);
            $url=$nimble_api->getOauth3Url();
        } catch (Exception $e) {
            return false;
        }

        return $url;
    }
    
    function getToken(){
        
        $token=false;
        
        if(Mage::getStoreConfig('payment/nimblepayments_checkout/token'))
            $token=true;
        
        return $token;
    }
}
