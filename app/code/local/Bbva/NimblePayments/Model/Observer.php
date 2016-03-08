<?php

class Bbva_NimblePayments_Model_Observer extends Mage_Payment_Model_Method_Abstract 
{
    /**
     *
     * @param Varien_Event_Observer $observer
     * @return object
     */

    public function nimbleCheckout(Varien_Event_Observer  $observer)
    {
        if(Mage::app()->getRequest()->getParam('order')){
            $orderID = Mage::app()->getRequest()->getParam('order');
            $key = Mage::app()->getRequest()->getParam('key');
            //TODO: Validate key param
            
                
            $order = Mage::getModel('sales/order');;
            //$incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $order->loadByIncrementId($orderID);
            $invoice = $order->prepareInvoice();
				$invoice->register()->capture();
				Mage::getModel('core/resource_transaction')
				->addObject($invoice)
				->addObject($invoice->getOrder())
				->save();
        }
        return $this;
    }
}