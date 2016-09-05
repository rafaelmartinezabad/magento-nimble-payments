<?php

class Bbva_NimblePayments_CheckoutController extends Bbva_NimblePayments_Controller_Abstract
{
    protected $_redirectBlockType = 'nimblepayments/checkout_redirect';
     public function failureAction()
    {   
            $connection = Mage::app()->getRequest()->getParam('connection');
            $error = Mage::app()->getRequest()->getParam('error');
            
            if(Mage::getSingleton('checkout/session')->getLastRealOrderId()){
                if ($lastQuoteId = Mage::getSingleton('checkout/session')->getLastQuoteId()){
                    $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
                    $quote->setIsActive(true)->save();
                }
            if(isset($connection)){    
                Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('Could not connect to the bank. Code ERR_PAG.'));
            }else if(isset($error))
                Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('An error has occurred. Code ERR_PAG.'));
            else
                Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('Card payment was rejected. Please try again.'));
            
            //Cancel order
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper('core')->__('Card payment was rejected.'));
            $order->save();
            
            $this->_redirect('checkout/cart'); //Redirect to cart
            return;
            }
    }  
    
    /**
     * Order success action
     */
    public function storedcardsAction() {
        $_max_attemps_to_request_status = 5;
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $url_params = $this->getRequest()->getParams();
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        $lastOrderStatusNimble = "PENDING";
        try{
            $NimbleApi = new NimbleAPI(array(
                'clientId' => Mage::getStoreConfig('payment/nimblepayments_checkout/merchant_id'),
                'clientSecret' => Mage::getStoreConfig('payment/nimblepayments_checkout/secret_key')
            ));
            $i = 0; $finish = false;
            do {
                $response = NimbleAPIPayments::getPaymentStatus($NimbleApi, null, $url_params['order']);
                $lastOrderStatusNimble = $response['data']['details'][0]['state'];
                if ($lastOrderStatusNimble != "PENDING") { sleep(1); }
                $i++;
            } while ($lastOrderStatusNimble == "PENDING" && $i < $_max_attemps_to_request_status);
        }  catch (Exception $e){
            Mage::throwException($e->getMessage());
        }
        switch ($lastOrderStatusNimble) {
            case 'PENDING':
                $this->loadLayout();
                $this->renderLayout();
                break;
            case 'SETTLED':
            case 'ON_HOLD':
                $this->_redirect('checkout/onepage/success', $url_params);
                break;
            default: // error
                $this->_redirect('nimblepayments/checkout/failure');
                break;
        }
        return;
    }

}
