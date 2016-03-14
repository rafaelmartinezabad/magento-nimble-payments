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
            $order = Mage::getModel('sales/order');;
            //$incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $order->loadByIncrementId($orderID);
            $invoice = $order->prepareInvoice();
				$invoice->register()->capture();
				Mage::getModel('core/resource_transaction')
				->addObject($invoice)
				->addObject($invoice->getOrder())
				->save();
        }
        return $this;
    }
      public function configNimble($observer){

          require_once dirname(__FILE__) .'/lib/Nimble/base/NimbleAPI.php';

           $params = array(
            'clientId' => trim(html_entity_decode(Mage::getStoreConfig('payment/nimblepayments_checkout/merchant_id'))),
            'clientSecret' => trim(html_entity_decode(Mage::getStoreConfig('payment/nimblepayments_checkout/secret_key'))),
            'mode' => 'demo'
            );
             $Switch = new Mage_Core_Model_Config();
        try {
            $nimbleApi = new NimbleAPI($params);
            //TODO: Verificación credenciales mediante llamada a NimbleApi cuando se actualize el SDK
            $nimbleApi->uri .= 'check';
            $nimbleApi->method = 'GET';
            $response = $nimbleApi->rest_api_call();
            if ( isset($response) && isset($response['result']) && isset($response['result']['code']) && 200 == $response['result']['code'] ){
               // $Switch->saveConfig('payment/nimblepayments_checkout/active', 1, 'default', 0);

            } else{
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('core')->__('Datos de pasarela no válidos para aceptar pagos. Asegúrate de que no sean de una pasarela de Test.'));
                $Switch->saveConfig('payment/nimblepayments_checkout/active', 0, 'default', 0);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('core')->__('Datos de pasarela no válidos para aceptar pagos. Asegúrate de que no sean de una pasarela de Test.'));
                $Switch->saveConfig('payment/nimblepayments_checkout/active', 0, 'default', 0);
        }
        return $this;
                    
      }
    
    
}