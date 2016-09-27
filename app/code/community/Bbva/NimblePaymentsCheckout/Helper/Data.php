<?php

class Bbva_NimblePaymentsCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function fasterPageCheckoutEnabled()
    {
        return Mage::getStoreConfig('payment/nimblepayments_checkout/active') 
                && Mage::getStoreConfig('payment/nimblepayments_checkout/fasterpage_checkout_enabled');
    }
}
