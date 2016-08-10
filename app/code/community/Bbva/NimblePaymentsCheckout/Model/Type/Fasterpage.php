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
                
                //Get current Shipping Address
                $shippingAddress = $this->getQuote()->getShippingAddress();
                
                //Calculate availables Shipping Methods for current shipping address and save
                $shippingAddress->collectShippingRates()->save();
                
                //Get the list of shipping methods
                $rates = $shippingAddress->getGroupedAllShippingRates();
                
                //Choose cheaper shipping method
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
                
                //Stored Card Payment
                $storedCards = Mage::getSingleton('Bbva_NimblePayments_Model_StoredCard')->getListStoredCards();
                foreach ( $storedCards as $card_id => $card ){
                    if ($card['default']){
                        $payment['storedcard'] = base64_encode(json_encode($storedCards[$card_id]));
                        break;
                    }
                }
                parent::savePayment($payment);
                break;
            default:
                return false;
        }
        
        return true;
    }
}