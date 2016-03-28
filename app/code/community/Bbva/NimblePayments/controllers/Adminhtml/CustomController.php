<?php
 
class Bbva_NimblePayments_Adminhtml_CustomController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('nimble')
            ->_title($this->__('Balance'));

        $block = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'MenuNimble',
            array('template' => 'nimblepaymentsadmin/menunimble.phtml')
            )
            ->setData('url', $this->getOauth3Url())
            ->setData('token', $this->getToken());    

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