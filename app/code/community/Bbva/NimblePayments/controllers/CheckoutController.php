<?php

class Bbva_NimblePayments_CheckoutController extends Bbva_NimblePayments_Controller_Abstract
{
    protected $_redirectBlockType = 'nimblepayments/checkout_redirect';
    public function failureAction()
    {   
            $connection = Mage::app()->getRequest()->getParam('connection');
            $error = Mage::app()->getRequest()->getParam('error');
            
            if(Mage::getSingleton('checkout/session')->getLastRealOrderId()){
                if ($lastQuoteId = Mage::getSingleton('checkout/session')->getLastQuoteId()){
                    $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
                    $quote->setIsActive(true)->save();
                }
            if(isset($connection)){    
                Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('Could not connect to the bank. Code ERR_PAG.'));
            }else if(isset($error))
                Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('An error has occurred. Code ERR_PAG.'));
            else
                Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('Card payment was rejected. Please try again.'));
            
            //Cancel order
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper('core')->__('Card payment was rejected.'));
            $order->save();
            
            $this->_redirect('checkout/cart'); //Redirect to cart
            return;
            }
    }

    /**
     * Order storedcards action
     */
    public function storedcardsAction() {
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $url_params = $this->getRequest()->getParams();
        $checkout = Mage::getModel('nimblepayments/checkout');
        $orderID = Mage::app()->getRequest()->getParam('order');
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($orderID);
        $payment = $order->getPayment();
        $transaction_id = $payment->getAdditionalInformation('np_transaction_id');
        $statusNimble = $checkout->getNimbleStatus($transaction_id);
        switch ($checkout->doActionBeforeStatus($orderID, $statusNimble)) {
            case "OK":
                $this->_redirect('checkout/onepage/success', $url_params);
                break;
            case "KO":
                $this->_redirect('nimblepayments/checkout/failure');
                break;
            default: // pending or other
                $this->loadLayout();
                $this->renderLayout();
                break;
        }
        return;
    }

}
