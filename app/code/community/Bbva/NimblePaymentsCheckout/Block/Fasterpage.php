<?php


class Bbva_NimblePaymentsCheckout_Block_Fasterpage extends Mage_Checkout_Block_Onepage
{
    public function getActiveStep()
    {
        //TODO ***********************************************
        return $this->isCustomerLoggedIn() ? 'shipping' : 'login';
    }
}
