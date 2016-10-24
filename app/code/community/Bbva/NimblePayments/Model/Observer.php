<?php

class Bbva_NimblePayments_Model_Observer extends Mage_Payment_Model_Method_Abstract 
{
    /**
     *
     * @param Varien_Event_Observer $observer
     * @return object
     */

    public function nimbleCheckout($observer)
    {
        $orderID = Mage::app()->getRequest()->getParam('order');
        $key_param = Mage::app()->getRequest()->getParam('key');

        if( $orderID && $key_param == Mage::getSingleton('adminhtml/url')->getSecretKey('nimblepayments', $orderID) ){
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($orderID);
            $invoice = $order->prepareInvoice();
            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
            $order->sendNewOrderEmail();
        }
      
        return $this;
    }


    public function configNimble($observer){

        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPICredentials.php';

        $Switch = new Mage_Core_Model_Config();
        try {
            $checkout = Mage::getModel('nimblepayments/checkout');
            $params = array(
                'clientId' => $checkout->getMerchantId(),
                'clientSecret' => $checkout->getSecretKey(),
                'mode' => NimbleAPIConfig::MODE
            );
            $nimbleApi = new NimbleAPI($params);
            $response = NimbleAPIEnvironment::verification($nimbleApi);
            if ( isset($response) && isset($response['result']) && isset($response['result']['code']) && 200 == $response['result']['code'] ){
                //correct
            } else {
                if (Mage::getStoreConfig('payment/nimblepayments_checkout/active')!= 0){
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('core')->__('Data invalid gateway to accept payments.'));
                    $Switch->saveConfig('payment/nimblepayments_checkout/active', 0, 'default', 0)
                        ->removeCache();
                }  
            }
        } catch (Exception $e) {
            if (Mage::getStoreConfig('payment/nimblepayments_checkout/active')!= 0){
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('core')->__('Data invalid gateway to accept payments.'));
                $Switch->saveConfig('payment/nimblepayments_checkout/active', 0, 'default', 0)
                    ->removeCache();
            }
        }

        return $this;
    }

    public function saveUserLoginSession($observer){
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        
        $checkout = Mage::getModel('nimblepayments/checkout');
        $token = Mage::getStoreConfig('payment/nimblepayments_checkout/token');
        $refreshToken = Mage::getStoreConfig('payment/nimblepayments_checkout/refreshToken');
        if( $token && $refreshToken ){
            try {
                $params = array(
                    'clientId' => $checkout->getMerchantId(),
                    'clientSecret' => $checkout->getSecretKey(),
                    'token' => $token,
                    'refreshToken' =>Mage::getStoreConfig('payment/nimblepayments_checkout/refreshToken'),
                    'mode' => NimbleAPIConfig::MODE
                );

                $nimble_api = new NimbleAPI($params);
                $options = array(
                    'token' => $nimble_api->authorization->getAccessToken(),
                    'refreshToken' => $nimble_api->authorization->getRefreshToken()
                );

                //guardar los tokens (OAUTH3)
                $Switch = new Mage_Core_Model_Config();
                $Switch->saveConfig('payment/nimblepayments_checkout/token', $options['token'], 'default', 0)
                   ->saveConfig('payment/nimblepayments_checkout/refreshToken', $options['refreshToken'], 'default', 0)
                    ->removeCache();
            } catch (Exception $e) {
                //Borramos los tokens (OAUTH3)
                $Switch = new Mage_Core_Model_Config();
                $Switch->deleteConfig('payment/nimblepayments_checkout/token')
                   ->deleteConfig('payment/nimblepayments_checkout/refreshToken')
                   ->removeCache();
            }
        }
        //Check orders with pending status
        $this->checkPendingNimbleOrders();
    }

    private function checkPendingNimbleOrders() {
        $orders = Mage::getModel('sales/order')->getCollection()->join(
                array('payment' => 'sales/order_payment'),
                'main_table.entity_id=payment.parent_id',
                array('payment_method' => 'payment.method'))
            ->addFieldToFilter('payment.method', 'nimblepayments_checkout')
            ->addFieldToFilter('status', 'pending_nimble')
            ->addAttributeToSort('created_at', 'DESC');

        $checkout = Mage::getModel('nimblepayments/checkout');
        foreach ($orders as $order) {
            $order_id = $order->getIncrementId();
            $payment = $order->getPayment();
            $transaction_id = $payment->getAdditionalInformation('np_transaction_id');
            $statusNimble = $checkout->getNimbleStatus($transaction_id);
            $checkout->doActionBeforeStatus($order_id, $statusNimble, false);
        }
    }
    
}