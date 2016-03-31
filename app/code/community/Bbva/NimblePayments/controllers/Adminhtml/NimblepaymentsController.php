<?php
 
class Bbva_NimblePayments_Adminhtml_NimblepaymentsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('nimble');
        
        //TODO GET RESUMEN
        
        $block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'MenuNimble',
            array('template' => 'nimblepaymentsadmin/authorization.phtml')
            )
            ->setData('url', $this->getOauth3Url());

        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
        
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