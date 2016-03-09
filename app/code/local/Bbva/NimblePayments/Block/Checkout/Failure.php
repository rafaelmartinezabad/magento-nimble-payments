<?php

class Bbva_NimblePayments_Block_Checkout_Failure extends Mage_Core_Block_Template
{
    /**
     *  Return Error message
     *
     *  @return	  string
     */
    public function getErrorMessage ()
    {
        $msg = Mage::getSingleton('checkout/session')->getNimbleErrorMessage();
        Mage::getSingleton('checkout/session')->unsNimbleErrorMessage();
        return $msg;
    }

    /**
     * Get continue shopping url
     */
    public function getContinueShoppingUrl()
    {
                    error_log("********* incrementID");


         /*   $session = Mage::getSingleton('customer/session');
                if($session->getData('redirect_to_checkout'))
            {
                            error_log("********* incrementID2");
                 $session->unsetData('redirect_to_checkout');
               return Mage::app()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage'));
            }*/
           // return Mage::app()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage')
        return Mage::getUrl('checkout/onepage');
    }
}
