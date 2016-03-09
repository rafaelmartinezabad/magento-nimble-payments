<?php
class Bbva_NimblePayments_Block_Checkout_Response extends Mage_Core_Block_Template
{
    /**
     *  Return Error message
     *
     *  @return	  string
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('nimblepayments/checkout/response.phtml');
    }
    
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

        return Mage::getUrl('checkout/cart');
        
    }
}
