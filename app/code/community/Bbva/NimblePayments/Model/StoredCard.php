<?php

require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIStoredCards.php';
    
class Bbva_NimblePayments_Model_StoredCard extends Mage_Payment_Model_Method_Abstract {
    
    /*
     * Get all stored customer cards
     */
    public function getListStoredCards($NimbleApi, $cardHolderId){
        
        $cards = array();
        
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
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
    
}

