<?php

require_once 'Mage/Checkout/controllers/OnepageController.php';

class Bbva_NimblePaymentsCheckout_FasterpageController extends Mage_Checkout_OnepageController
{
    /**
     * Check if a guest can proceed to the checkout
     *
     * @return boolean
     */
    protected function _canShowForUnregisteredUsers()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()){
            return true;
        }
        Mage::getSingleton('customer/session')->addError(
            Mage::helper('checkout')->__('Please login or register to continue to the checkout')
        );
        $this->_redirect('customer/account/login');
        $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        //return true to the caller method _preDispatch so that it doesn't redirect to the 404 page
        return true;
    }


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
        
        //TODO ***********************************************
        $this->getFasterpage()->initBilling();
        
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }

}
