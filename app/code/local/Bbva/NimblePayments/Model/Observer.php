<?php

class Bbva_NimblePayments_Model_Observer extends Mage_Payment_Model_Method_Abstract 
{
    /**
     *
     * @param Varien_Event_Observer $observer
     * @return object
     */

    public function nimbleCheckout($observer)
    {
        $orderID = Mage::app()->getRequest()->getParam('order');
        $key_param = Mage::app()->getRequest()->getParam('key');

        if( $orderID && $key_param == Mage::getSingleton('adminhtml/url')->getSecretKey('nimblepayments', $orderID) ){
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