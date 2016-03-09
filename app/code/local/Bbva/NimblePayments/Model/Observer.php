<?php

class Bbva_NimblePayments_Model_Observer extends Mage_Payment_Model_Method_Abstract 
{
    /**
     *
     * @param Varien_Event_Observer $observer
     * @return object
     */
    //*******   pay sucess *****************
    public function nimbleCheckout(Varien_Event_Observer  $observer)
    {
        if(Mage::app()->getRequest()->getParams('order')){
           // $nombre =Mage::app()->getRequest()->getParams('order'); 
            
        $order = Mage::getModel('sales/order');;
        $incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order->loadByIncrementId($incrementId);
        $invoice = $order->prepareInvoice();
				$invoice->register()->capture();
				Mage::getModel('core/resource_transaction')
				->addObject($invoice)
				->addObject($invoice->getOrder())
				->save();
        
        //return $this;
        }
        /*else {
            Mage_Core_Controller_Varien_Action::_redirect( 'checkout/onepage/failure' );
        }*/
    }

    
}