<?php

class Bbva_NimblePayments_CheckoutController extends Bbva_NimblePayments_Controller_Abstract
{
    protected $_redirectBlockType = 'nimblepayments/checkout_redirect';
    
    public function responseAction()
    {
        error_log("entra en repsonse");
        $responseParams = $this->getRequest()->getParams();         
        if(!empty($responseParams['t'])) {
            $infoModel = Mage::getModel('nimblepayments/info');
            $checkoutModel = Mage::getModel('nimblepayments/checkout');
            $response = Mage::helper('nimblepayments')->getTransactionDetails($responseParams['t']);            
            if($response->ErrorCode==0) {
                $transaction = $response->Transactions[0];
                if(Mage::helper('nimblepayments')->validateOrder($transaction->MerchantTrns) && $transaction->StatusId=='F') {
                    $checkoutModel->afterSuccessOrder($transaction);
                    $this->_redirect('checkout/onepage/success');
                    return;
                }
                else {
                    Mage::getSingleton('core/session')->addError($this->__('Invalid transaction!'));
                    $this->_redirect('checkout/cart');
                    return;
                }
            }
            else {
                if(!empty($response->Transactions[0])) {
                    $transaction = $response->Transactions[0];
                    $order = Mage::getModel('sales/order');
                    $order->loadByIncrementId($transaction->MerchantTrns);
                    $order->addStatusToHistory($order->getStatus(), $response->ErrorText);
                    $order->save();
                    $error = $response->ErrorText;
                }
                else {
                    $error = $this->__('There is an error occured during transaction. Please try again!');
                }
                Mage::getSingleton('core/session')->addError($error);
                $this->_redirect('checkout/cart');
                return;
            }
        }
        else {
            Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('Trasaction is failed!'));
            $this->_redirect('checkout/cart');
            return;
        }
    }
    
}
