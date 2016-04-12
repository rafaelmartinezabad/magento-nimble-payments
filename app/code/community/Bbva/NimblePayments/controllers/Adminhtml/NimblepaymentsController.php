<?php
 
class Bbva_NimblePayments_Adminhtml_NimblepaymentsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('nimble');
        
        $invalid_token = $this->getToken() ? false : true;
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIReport.php';
        
        try {
            $params = array(
                'clientId' => Mage::getStoreConfig('payment/nimblepayments_checkout/merchant_id'),
                'clientSecret' => Mage::getStoreConfig('payment/nimblepayments_checkout/secret_key'),
                'token' => Mage::getStoreConfig('payment/nimblepayments_checkout/token'),
                'mode' => NimbleAPIConfig::MODE
            );
            $nimble_api = new NimbleAPI($params);
            $commerces = NimbleAPIReport::getCommerces($nimble_api);
            if (!isset($commerces['error'])){
                foreach ($commerces as $IdCommerce => $data){
                    $title = $data['name'];
                    $summary = NimbleAPIReport::getSummary($nimble_api, $IdCommerce);
                    
                    $block = $this->getLayout()->createBlock(
                        'Mage_Core_Block_Template',
                        'MenuNimble',
                        array('template' => 'nimblepaymentsadmin/summary.phtml')
                        )
                        ->setData('summary', $summary)
                            ->setData('title', $title);

                    $this->getLayout()->getBlock('content')->append($block);
                    $this->renderLayout();
                    
                }
            } else {
                $invalid_token = true;
            }

        } catch (Exception $e) {
            $invalid_token = true;
        }
        
        if ($invalid_token){
            $block = $this->getLayout()->createBlock(
                'Mage_Core_Block_Template',
                'MenuNimble',
                array('template' => 'nimblepaymentsadmin/authorization.phtml')
                )
                ->setData('url', $this->getOauth3Url());

            $this->getLayout()->getBlock('content')->append($block);
            $this->renderLayout();
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
    
    
    /*public function listAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('mycustomtab')
            ->_title($this->__('List Action'));
 
        // my stuff
 
        $this->renderLayout();
    }*/
    
}
