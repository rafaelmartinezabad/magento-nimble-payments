<?php

class Bbva_NimblePayments_CheckoutController extends Bbva_NimblePayments_Controller_Abstract
{
    protected $_redirectBlockType = 'nimblepayments/checkout_redirect';
     public function failureAction()
    {   
            $connection = Mage::app()->getRequest()->getParam('connection');
            $error = Mage::app()->getRequest()->getParam('error');
        
            error_log("connection");
            error_log($connection);
            error_log(isset($connection));
            error_log($connection==false);
            error_log("error");
            error_log($error);
            
            
            
            
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

            $this->_redirect('checkout/cart'); //Redirect to cart
            return;
            }
    }  
    
   

}
