<?php

class Bbva_NimblePayments_Model_Checkout extends Mage_Payment_Model_Method_Abstract
{
    
    protected $_code  = 'nimblepayments_checkout';

    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    protected $_formBlockType = 'nimblepayments/checkout_form';
    protected $_paymentMethod = 'checkout';
    protected $_infoBlockType = 'nimblepayments/payment_info';

    protected $_order;
   
    protected $_paymentUrl = null;
    
    protected $_merchantId = null;
    protected $_secretKey = null;
    protected $_token = null;
      
    public function refund(Varien_Object $payment, $amount)
    {
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        
        if (!$this->canRefund()) {
            Mage::throwException(Mage::helper('payment')->__('Refund action is not available.')); // tr000
        }
        if (!$this->getToken()) {
            Mage::throwException(Mage::helper('payment')->__('Refund Failed').": ".Mage::helper('payment')->__('You must authorize the advanced options Nimble Payments.')); // tr001 tr002
        }
        
        $transaction_id = $payment->getAdditionalInformation('np_transaction_id');
        
        $otp_token = $this->getOTPToken();
        try {
            $params = array(
                'clientId' => $this->getMerchantId(),
                'clientSecret' => $this->getSecretKey(),
                'token' => $otp_token,
                'mode' => NimbleAPIConfig::MODE
            );
            
            $nimble_api = new NimbleAPI($params);
            $total_refund = $amount;
            
            $refund = array(
                'amount' => $total_refund * 100,
                'concept' => Mage::getSingleton('adminhtml/session')->getCommentText(),
                'reason' => 'REQUEST_BY_CUSTOMER'
            );
            
            $response = NimbleAPIPayments::sendPaymentRefund($nimble_api, $transaction_id, $refund);
            error_log(print_r($response, true));
        } catch (Exception $e) {
            $message = Mage::helper('payment')->__('Refund Failed').': '; // tr001
            Mage::throwException($message);
        }
        
        //OPEN OPT
        if (isset($response['result']) && isset($response['result']['code']) && 428 == $response['result']['code']
                && isset($response['data']) && isset($response['data']['ticket']) && isset($response['data']['token']) ){
            $ticket = $response['data']['ticket'];
            $credit_memo = Mage::app()->getRequest()->getParam('creditmemo');
            $form_key = Mage::app()->getRequest()->getParam('form_key');
            $otp_info = array(
                'action'    =>  'refund',
                'ticket'    =>  $ticket,
                'token'     =>  $response['data']['token'],
                'creditmemo' => $credit_memo,
                'form_key'   => $form_key,
                'REQUEST_URI' => $_SERVER['REQUEST_URI'],
            );
            //update_user_meta($user_id, 'nimblepayments_ticket', $otp_info);
            //guardar los tokens (OAUTH3)
            $serialized_ticket = serialize($otp_info);
            $Switch = new Mage_Core_Model_Config();
            $Switch->saveConfig('payment/nimblepayments_checkout/ticket', $serialized_ticket, 'default', 0);
            
            $back_url = Mage::app()->getWebsite(true)->getDefaultStore()->getUrl('', array('_direct'=>'nimblepayments/oauth3'));
            $url_otp = NimbleAPI::getOTPUrl($ticket, $back_url);
            header('Location: ' . $url_otp);
            die();
        } else if (!isset($response['data']) || !isset($response['data']['refundId'])){
            $message = Mage::helper('payment')->__('Refund Failed').': '; // tr001
            
            if ( isset($response['result']) && isset($response['result']['info']) ){
                $message .= $response['result']['info'];
            }
            else if (isset($response['error'])) {
                $message .= $response['error'];
            }
           Mage::throwException($message);
        }
        
        return $this;
    }
    
    public function getOTPToken()
    {
        if (Mage::app()->getRequest()->has('ticket')){
            $serialized_ticket = Mage::getStoreConfig('payment/nimblepayments_checkout/ticket');
            $otp_info = unserialize($serialized_ticket);
            $ticket = Mage::app()->getRequest()->getParam('ticket');
            $result = Mage::app()->getRequest()->getParam('result');
            if ($ticket == $otp_info['ticket'] && 'OK' == $result ){
                return $otp_info['token'];
            } else {
                $message = Mage::helper('payment')->__('Refund Failed'); // tr001
                Mage::throwException($message);
            }
        }
        return $this->getToken();
    }
    
    public function getOrder()
    {
        if (!$this->_order) {
            $paymentInfo = $this->getInfoInstance();
            $this->_order = Mage::getModel('sales/order')
                            ->loadByIncrementId($paymentInfo->getOrder()->getRealOrderId()); 

        }
        return $this->_order;
    }
    
   
    
    public function getMerchantId()
    {
        if ($this->_merchantId === null) {
            $this->_merchantId = Mage::getStoreConfig('payment/' . $this->getCode() . '/merchant_id');
        }
        return $this->_merchantId;
    }

    public function getSecretKey()
    {
        if ($this->_secretKey === null) {
            $this->_secretKey = Mage::getStoreConfig('payment/' . $this->getCode() . '/secret_key');
        }
        return $this->_secretKey;
    }
    
    public function getSourceCode()
    {
        $source_code = Mage::getStoreConfig('payment/' . $this->getCode() . '/source_code');        
        return $source_code;            
    }
    
    public function getToken()
    {
        if ($this->_token === null) {
            $this->_token = Mage::getStoreConfig('payment/' . $this->getCode() . '/token');
        }
        return $this->_token;
    }
    
    public function getAmount()
    {
            $_amount = (double)$this->getOrder()->getBaseGrandTotal();           
            return $_amount*100; 
    }
    
    public function validate()
    {           
          
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $currency_code = $paymentInfo->getOrder()->getBaseCurrencyCode();
        } else {
            $currency_code = $paymentInfo->getQuote()->getBaseCurrencyCode();
        }     
        if($paymentInfo->getLang()!='') {
            $paymentInfo->setAdditionalInformation('lang',$paymentInfo->getLang());
            //Mage::throwException($paymentInfo->getLang());
        }
        
        //StoredCard Fields
        $storedcard_fields = array(
            'maskedPan',
            'cardBrand'
        );
        foreach ($storedcard_fields as $field){
            if ($paymentInfo->getData($field)){
                $paymentInfo->setAdditionalInformation($field,$paymentInfo->getData($field));
            }
        }

        $additionalFields = array(
            'changeAddress'
        );
        foreach ($additionalFields as $field){
            if ($paymentInfo->getData($field)){
                $paymentInfo->setAdditionalInformation($field,$paymentInfo->getData($field));
            }
        }

        return true;
    }
    
    public function getCoin()
    {           
          
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $currency_code = $paymentInfo->getOrder()->getBaseCurrencyCode();
        } else {
            $currency_code = $paymentInfo->getQuote()->getBaseCurrencyCode();
        }     
        if($paymentInfo->getLang()!='') {
            $paymentInfo->setAdditionalInformation('lang',$paymentInfo->getLang());
            //Mage::throwException($paymentInfo->getLang());
        }
        
        return $currency_code;
    }

    /**
     * Similar Bbva_NimblePayments_Controller_Abstract redirectAction
     * to avoid redirect page
     */
    public function getOrderPlaceRedirectUrl()
    {
        $order = $this->getLastOrder();

        $session = Mage::getSingleton('checkout/session');
        $session->setNimbleQuoteId($session->getQuoteId());
        $session->setNimbleRealOrderId($order->getIncrementId());
        $session->unsQuoteId();

        if ($order->getStatus() == 'pending'){
            $order->addStatusToHistory('pending_nimble', Mage::helper('core')->__('Order pending to Nimble Payments validation.')); // tr003
            $order->save();
        }

        $this->getInfoInstance()->setOrder($order);
        return $url = $this->getGatewayRedirectUrl();
    }
    
    public function isTestMode()
    {
        return Mage::getStoreConfig('payment/' . $this->getCode() . '/test_mode'); 
    }
    
    public function getGatewayRedirectUrl()
    {
        if(!is_null($this->_paymentUrl)) {          
            $url = $this->_paymentUrl;
        }
        else {    
              $url = $this->getCheckoutUrl();

        }
        return $url;
    }
    
    public function getProdID(){
        $paymentInfo = $this->getInfoInstance();
        $order_number = $paymentInfo->getOrder()->getRealOrderId();
        
        return $order_number;
    }
    
    public function paymentStoredCard(){
        $url='';
        try{
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customerData->getId();
            $NimbleApi = Mage::getSingleton('Bbva_NimblePayments_Model_Checkout')->getNimble();
            $storedCardPaymentInfo = array(
                'amount'       => $this->getAmount(),
                'currency'     => $this->getCoin(),
                'merchantOrderId' => $this->getProdID(),
                'cardHolderId' => $customerId
            );
            
            $preorder = NimbleAPIStoredCards::preorderPayment($NimbleApi, $storedCardPaymentInfo);
            //Save transaction_id to this order
            if ( isset($preorder["data"]) && isset($preorder["data"]["id"])){
                $transaction_id = $preorder["data"]["id"];
                //Save transaction_id
                $storedCardPaymentInfo = $this->getOrder()->getPayment();
                $storedCardPaymentInfo->setAdditionalInformation('np_transaction_id', $transaction_id);
                $storedCardPaymentInfo->save();
                $order_id = $this->getProdID();
                $key = Mage::getSingleton('adminhtml/url')->getSecretKey('nimblepayments', $order_id);
                $url = Mage::getUrl('nimblepayments/checkout/storedcards', array('order' => $order_id, 'key' => $key, 'storedcard' => 'true'));

                $response = NimbleAPIStoredCards::confirmPayment($NimbleApi, $preorder["data"]);
                //TIMEOUT CONTROL ON checkout/onepage/success PAGE
            }else{
                $url = Mage::getUrl('nimblepayments/checkout/failure');
            }
        } catch (Exception $ex) {
            $url = Mage::getUrl('nimblepayments/checkout/failure');
        }
        
        return $url;
    }
    
    public function getCheckoutUrl()
    {
        $url = '';
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIStoredCards.php';

        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $vpcInfo = Mage::getModel('nimblepayments/info');
            $payment = $this->getInfoInstance();
            $additionalFields = $vpcInfo->getAdditionalFields($payment, false);
            $isChangeShippingAddress = $additionalFields['changeAddress'];
            $info = $vpcInfo->getPublicPaymentInfo($payment, false);
            
            if( isset($info['maskedPan']) && !empty($info['maskedPan']) ){
                return $this->paymentStoredCard();
            }
        }
        
        $order_id = $this->getProdID();
        $key = Mage::getSingleton('adminhtml/url')->getSecretKey('nimblepayments', $order_id);
        
        $payment = array(
                'amount' => $this->getAmount(),
                'currency' => $this->getCoin(),
                'merchantOrderId' => $order_id,
                'paymentSuccessUrl' => Mage::getUrl('checkout/onepage/success', array('order' => $order_id, 'key' => $key)),
                'paymentErrorUrl' =>Mage::getUrl('nimblepayments/checkout/failure')
        );
        
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customerData->getId();
            $payment['cardHolderId'] = $customerId;
        }
        
        $params = array(
                'clientId' => $this->getMerchantId(),
                'clientSecret' =>$this->getSecretKey(),
                'mode' => NimbleAPIConfig::MODE
        );

        /* High Level call */
        try{
            //throw new Exception('División por cero.');
            $NimbleApi = new NimbleAPI($params);
            $p = new NimbleAPIPayments();
            $response = $p->SendPaymentClient($NimbleApi, $payment);
            if(isset($response["data"]) && isset($response["data"]["paymentUrl"])){
                $url = $response["data"]["paymentUrl"];
                $transaction_id = $response["data"]["id"];
                //Save transaction_id
                $payment = $this->getOrder()->getPayment();
                $payment->setAdditionalInformation('np_transaction_id', $transaction_id);
                $payment->save();
                //Delete cards if change the shipping address
                if (isset($isChangeShippingAddress) && $isChangeShippingAddress['value']) {
                    Mage::getSingleton('Bbva_NimblePayments_Model_StoredCard')->deleteStoredCards();
                }
            }else {
                $url=$payment["paymentErrorUrl"].'?error=false';
            }
            
        }  catch (Exception $e){
            $url=$payment["paymentErrorUrl"].'?connection=false';
            
        }
        
        return $url;    
    }

    /*
     * Get if change the shipping address
     */
    public function isChangeShippingAddress() {
        $currentAddress = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
        $hashCurrentAddress = $this->toHash($currentAddress);
        $lastAddress = $this->getLastOrder()->getShippingAddress();
        $hashLastAddress = $this->toHash($lastAddress);
        if (is_null($hashLastAddress)) { return false; }
        return ($hashCurrentAddress != $hashLastAddress);
    }

    /*
     * Get last order of the current customer
     */
    private function getLastOrder() {
        $lastOrder = null;
        try{
            $_customer = Mage::getSingleton('customer/session')->getCustomer();
            $lastOrder = Mage::getModel('sales/order')->getCollection()->join(
                    array('payment' => 'sales/order_payment'),
                    'main_table.entity_id=payment.parent_id',
                    array('payment_method' => 'payment.method'))
                ->addFieldToFilter('payment.method', 'nimblepayments_checkout')
                ->addFieldToFilter('customer_id', $_customer->getId())
                ->addAttributeToSort('created_at', 'DESC')
                ->setPageSize(1)
                ->getFirstItem();
         } catch (Exception $e){
            Mage::throwException($e->getMessage());
        }
        return $lastOrder;
    }

    /*
     * Get hash location customer
     */
    private function toHash($address = null) {
        if (empty($address) || empty($address->getFirstname())) { return null; }
        $location = $address->getFirstname()." ".$address->getLastname().", ".$address->getStreet(-1).", ".$address->getCity().", ".$address->getRegion()." ".$address->getPostcode().", ".$address->getCountryModel()->getIso3Code();
        return substr( md5( $location ), 0, 12 );
    }

    /**
     * Ask nimble of status transaction
     * @param order_id, _max_attemps_to_request_status = 5
     */
    public function getNimbleStatus($merchantOrderId, $_max_attemps_to_request_status = 5) {
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
                $response = NimbleAPIPayments::getPaymentStatus($NimbleApi, null, $merchantOrderId);
                if ( isset($response['data']) && isset($response['data']['details']) && count($response['data']['details']) ){
                    $lastOrderStatusNimble = $response['data']['details'][0]['state'];
                } elseif ( isset($response['result']) && isset($response['result']['code']) && 404 == $response['result']['code'] ) {
                    $lastOrderStatusNimble = 'NOT_FOUND';
                }
                $i++;
            } while ($lastOrderStatusNimble == "PENDING" && $i < $_max_attemps_to_request_status);
        }  catch (Exception $e){
            Mage::throwException($e->getMessage());
        }
        return $lastOrderStatusNimble;
    }

    /**
     * Action to do before get status order
     *
     * Status in Magento:
     *      Mage_Sales_Model_Order::STATE_NEW
     *      Mage_Sales_Model_Order::STATE_PENDING_PAYMENT
     *      Mage_Sales_Model_Order::STATE_PROCESSING
     *      Mage_Sales_Model_Order::STATE_COMPLETE
     *      Mage_Sales_Model_Order::STATE_CLOSED
     *      Mage_Sales_Model_Order::STATE_CANCELED
     *      Mage_Sales_Model_Order::STATE_HOLDED
     *
     * Necessary status:
     *      DENIED OR ABANDONED
     *      ERROR
     *
     * @param order_id, orderStatus, $isFront
     */
    public function doActionBeforeStatus($order_id, $orderStatus, $isFront = true) {
        $pageToLoad = "";
        $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        switch ($orderStatus){
            case 'SETTLED': // funds have been settled in the banking account
            case 'ON_HOLD': // Transaction has been processed and settlement is pending
                if ($isFront) { $pageToLoad = "OK"; } else {
                    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING, Mage::helper('core')->__('Card payment has been processed.')); // tr004
                }
                break;
            case 'ABANDONED': // Cardholder has not finished the payment procedure
            case 'DENIED': // Payment has been rejected by the processor
                // TODO: create DENIED and ABANDONED status
                $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper('core')->__('Card payment has been abandoned or denied.')); // tr005
                break;
            case 'CANCELLED': // Error in the payment gateway
                $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper('core')->__('Card payment was rejected.')); // tr006
                break;
            case 'ERROR': // Nimble internal error
                if ($isFront) { $pageToLoad = "KO"; } else {
                    // TODO: create ERROR status
                    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper('core')->__('Card payment had an unexpected error.')); //tr007
                }
                break;
            case 'NOT_FOUND': // Transaction not found, not Nimble state
            case 'PENDING': // Transaction has not been processed yet
            default: // PAGE_NOT_LOADED: Checkout page has not been loaded
                     // AND OTHER PAYMENT STATUS
                if ($isFront) { $pageToLoad = $orderStatus; }
                break;
        }
        $order->save();
        return $pageToLoad;
    }
    
    public function getParams(){
        
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        
        $params = array(
                'clientId' => $this->getMerchantId(),
                'clientSecret' =>$this->getSecretKey(),
                'mode' => NimbleAPIConfig::MODE
        );
        return $params;
    }
    
    public function getNimble(){
        
        $params = $this->getParams();
         
        try{
            $NimbleApi = new NimbleAPI($params);    
         } catch (Exception $e){
            Mage::throwException($e->getMessage());
        }
        return $NimbleApi;
    }
    
    /**
     * Get debug flag
     *
     * @return string
     */
    public function getDebug()
    {
        return Mage::getStoreConfig('payment/' . $this->getCode() . '/debug');
    }

    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setTransactionId($payment->getAdditionalInformation('np_transaction_id'));
        $payment->setStatus(self::STATUS_APPROVED);

        return $this;
    }

    public function cancel(Varien_Object $payment)
    {
        $payment->setStatus(self::STATUS_DECLINED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }
    
    public function getRedirectBlockType()
    {
        return $this->_redirectBlockType;
    }

    public function assignData($data)
    {
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIStoredCards.php';
        $info = $this->getInfoInstance();
        $card_base64 = isset($data['storedcard']) ? $data['storedcard'] : "";

        $info->addData(array("changeAddress" => $this->isChangeShippingAddress()));

        if(empty($card_base64)){
            $info->unsAdditionalInformation('maskedPan');
            $info->unsAdditionalInformation('cardBrand');
            
            return $this;
        }
        
        $result = array();   
        $card = json_decode(base64_decode($card_base64), true);
        
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $customerId = $customerData->getId();
            $card['cardHolderId'] = $customerId;
            
            try{
                if($card['default'] == false){
                    unset($card['default']);
                    $NimbleApi = Mage::getSingleton('Bbva_NimblePayments_Model_Checkout')->getNimble();
                    $result = NimbleAPIStoredCards::selectDefault($NimbleApi, $card);
                    if(isset($result['result']) && isset($result['result']['code']) && ($result['result']['code'] == 200)){
                       $info->addData($card);
                    } else {
                        throw new Exception();
                    }
                }else{
                    unset($card['default']);
                    $info->addData($card);
                }
            } catch (Exception $e){
                $this->setData('storedcards', Mage::getSingleton('Bbva_NimblePayments_Model_StoredCard')->getListStoredCards());
            }
        }

        return $this;
    }
    
    /**
     * Return payment method type string
     *
     * @return string
     */
    public function getPaymentMethodType()
    {
        return $this->_paymentMethod;
    }
    
    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        return Mage::helper('core')->__('Card payment'); // tr011
    }
    
}
