<?php

require_once 'Mage/Checkout/controllers/OnepageController.php';

class Bbva_NimblePaymentsCheckout_FasterpageController extends Mage_Checkout_OnepageController
{
    public function getFasterpage()
    {
        return Mage::getSingleton('nimblepaymentscheckout/type_fasterpage');
    }

    public function getOnepage()
    {
        return $this->getFasterpage();
    }
    
    /**
     * Checkout page
     */
    public function indexAction()
    {
        if (!Mage::helper('nimblepaymentscheckout')->fasterPageCheckoutEnabled()) {
            Mage::getSingleton('checkout/session')
                ->addError($this->__('Faster checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getFasterpage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                Mage::getStoreConfig('sales/minimum_order/error_message') :
                Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')
            ->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));
        $this->getFasterpage()->initCheckout();
        $this->loadLayout();
        
        //Skip Steps
        $continue = true;
        if (Mage::getSingleton('customer/session')->isLoggedIn()){
            foreach ($this->getFasterpage()->getCheckout()->getSteps() as $step_code => $step){
                if ($continue){
                    $continue = $this->getFasterpage()->skipStep($step_code);
                }
            }
        }
        
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }

}
