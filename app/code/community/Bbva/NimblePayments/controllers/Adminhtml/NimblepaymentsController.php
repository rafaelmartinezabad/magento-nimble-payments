<?php
 
class Bbva_NimblePayments_Adminhtml_NimblepaymentsController extends Mage_Adminhtml_Controller_Action
{

    public function anchorGetawayAction(){
        $this->_redirectUrl(Mage::helper("adminhtml")->getUrl("adminhtml/system_config/edit/section/payment") . "#payment_nimblepayments_checkout-head");
        return;
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
