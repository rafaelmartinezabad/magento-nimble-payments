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
                $billing['use_for_shipping'] = false;
                $result = parent::saveBilling($billing, $customerBillingAddressId);
                break;
            case 'shipping':
                $shipping = array();
                $customerBillingAddressId = $this->getCustomerSession()->getCustomer()->getDefaultBillingAddress()->getId();
                $customerShippingAddressId = $this->getCustomerSession()->getCustomer()->getDefaultShippingAddress()->getId();
                $shipping['same_as_billing'] = ( $customerBillingAddressId == $customerShippingAddressId ) ? true : false;
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
                            $shippingDescription = $_rate->getCarrierTitle() . ' - ' . $_rate->getMethodTitle();
                        }
                    }
                }
                parent::saveShippingMethod($shippingMethod);
                $shippingAddress->setShippingDescription($shippingDescription);
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