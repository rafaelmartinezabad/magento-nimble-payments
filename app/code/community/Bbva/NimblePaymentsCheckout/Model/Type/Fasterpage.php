<?php

class Bbva_NimblePaymentsCheckout_Model_Type_Fasterpage extends Mage_Checkout_Model_Type_Onepage
{
    public function skipStep($step_code)
    {
        if ($this->getCheckout()->getStepData($step_code, 'complete') == true) {
            return true;
        }
        switch ($step_code){
            case 'login':
                break;
            case 'billing':
                $billing = array();
                //TODO ***********************************************
                $customerBillingAddressId = $this->getCustomerSession()->getCustomer()->getDefaultBillingAddress()->getId();
                $customerShippingAddressId = $this->getCustomerSession()->getCustomer()->getDefaultShippingAddress()->getId();
                $billing['use_for_shipping'] = ( $customerBillingAddressId == $customerShippingAddressId ) ? true : false;
                $billing['same_as_billing'] = $billing['use_for_shipping'];
                $result = parent::saveBilling($billing, $customerBillingAddressId);
                //error_log(print_r($result, true));
                break;
            case 'shipping':
                $shipping = array();
                $shipping['same_as_billing'] = false;
                $customerShippingAddressId = $this->getCustomerSession()->getCustomer()->getDefaultShippingAddress()->getId();
                $result = parent::saveShipping($shipping, $customerShippingAddressId);
                //error_log(print_r($result, true));
                break;
            default:
                return false;
        }
        
        return true;
    }
}