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
        $key_param = Mage::app()->getRequest()->getParam('key');
        
        if( Mage::app()->getRequest()->getParam('order') && $key_param == Mage::getSingleton('adminhtml/url')->getSecretKey() ){
            $orderID = Mage::app()->getRequest()->getParam('order');
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
            
          error_log("entra en configNimbel");
          
          require_once dirname(__FILE__) .'/lib/Nimble/base/NimbleAPI.php';
          
           error_log(trim(html_entity_decode(Mage::getStoreConfig('payment/nimblepayments_checkout/secret_key'))));
           error_log(trim(html_entity_decode(Mage::getStoreConfig('payment/nimblepayments_checkout/merchant_id'))));
           
          
          
           $params = array(
            'clientId' => trim(html_entity_decode(Mage::getStoreConfig('payment/nimblepayments_checkout/merchant_id'))),
            'clientSecret' => trim(html_entity_decode(Mage::getStoreConfig('payment/nimblepayments_checkout/secret_key'))),
            'mode' => 'demo'
            );

        try {
            $nimbleApi = new NimbleAPI($params);
            //TODO: Verificación credenciales mediante llamada a NimbleApi cuando se actualize el SDK
            $nimbleApi->uri .= 'check';
            $nimbleApi->method = 'GET';
            $response = $nimbleApi->rest_api_call();
            if ( isset($response) && isset($response['result']) && isset($response['result']['code']) && 200 == $response['result']['code'] ){
                //$array[$this->status_field_name] = true;
          Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('core')->__('Valid Secret Key'));
            } else{
                //$array[$this->status_field_name] = false;
          Mage::getSingleton('adminhtml/session')->addError(Mage::helper('core')->__('Invalid Secret Key. Please refresh the page.'));
            }
        } catch (Exception $e) {
           // $array[$this->status_field_name] = false;
          Mage::getSingleton('adminhtml/session')->addError(Mage::helper('core')->__('Invalid Secret Key. Please refresh the page.'));

        }

        
        return $this;
          
          
           //$_keyErrorMsg = Mage::helper('adminhtml')->__('Invalid Secret Key. Please refresh the page.');
          
      }      
    
}