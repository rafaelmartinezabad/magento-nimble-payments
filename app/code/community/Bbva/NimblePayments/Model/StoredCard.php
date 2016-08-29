<?php

require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIStoredCards.php';
    
class Bbva_NimblePayments_Model_StoredCard extends Mage_Payment_Model_Method_Abstract {
    
    /*
     * Get all stored customer cards
     */
    public function getListStoredCards(){
        
        $cards = array();
        
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();

            if (Mage::getSingleton('Bbva_NimblePayments_Model_Checkout')->isChangeShippingAddress()) { 
                return array();
            }

            $customerId = $customerData->getId();
            try{
                $NimbleApi = Mage::getSingleton('Bbva_NimblePayments_Model_Checkout')->getNimble();
                $result = NimbleAPIStoredCards::getStoredCards($NimbleApi, $customerId);
                if(isset($result['data']) && isset($result['data']['storedCards'])){
                    $cards = $result['data']['storedCards'];
                }
            } catch (Exception $e){
                // warning: getStoredCard failed.
            }
        } 
        return $cards;
    }

    /*
     * delete all stored customer cards
     */
    public function deleteStoredCards() {
        $result = false;
        try{
            $NimbleApi = Mage::getSingleton('Bbva_NimblePayments_Model_Checkout')->getNimble();
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            $result = NimbleAPIStoredCards::deleteAllCards($NimbleApi, $customerId);
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }
        return $result;
    }

}

