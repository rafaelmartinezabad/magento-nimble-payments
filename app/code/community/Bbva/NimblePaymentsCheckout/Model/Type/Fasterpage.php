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
                break;
            case 'shipping_method':
                $shippingMethod = '';
                $lastPrice = 0;
                //Choose cheaper shipping method
                $rates = $this->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
                foreach ($rates as $code => $_rates){
                    foreach ($_rates as $_rate){
                        if ( $shippingMethod == '' || $lastPrice > $_rate->getPrice()){
                            $lastPrice = $_rate->getPrice();
                            $shippingMethod = $_rate->getCode();
                        }
                    }
                }
                parent::saveShippingMethod($shippingMethod);
                break;
            case 'payment':
                $payment = array('method' => 'nimblepayments_checkout');
                //TODO: Stored Card Payment
                parent::savePayment($payment);
                break;
            default:
                return false;
        }
        
        return true;
    }
}