<?php

class Bbva_NimblePayments_Model_Checkout extends Mage_Payment_Model_Method_Abstract
{
        
    protected $_code  = 'nimblepayments_checkout';

    protected $_isGateway               = false;
    protected $_canAuthorize            = false;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    protected $_formBlockType = 'nimblepayments/checkout_form';
    protected $_paymentMethod = 'checkout';
    protected $_infoBlockType = 'nimblepayments/payment_info';

    protected $_order;
    
    protected $_paymentUrl = null;

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
        $merchant_id = Mage::getStoreConfig('payment/' . $this->getCode() . '/merchant_id'); 
        return $merchant_id;
    }

    public function getSecretKey()
    {
        $secret_key = Mage::getStoreConfig('payment/' . $this->getCode() . '/secret_key');        
        return $secret_key;            
    }
    
    public function getSourceCode()
    {
        $source_code = Mage::getStoreConfig('payment/' . $this->getCode() . '/source_code');        
        return $source_code;            
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

    public function getOrderPlaceRedirectUrl()
    {
        return $url = Mage::getUrl('nimblepayments/' . $this->_paymentMethod . '/redirect');        
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
    
    /*public function getCreateOrderUrl()
    {
       
        if($this->isTestMode()) {
            $url = 'http://demo.nimblepayments.com/api/orders/';
        }
        else {                    
            $url = 'https://www.nimblepayments.com/api/orders/';
        }
        return $url;
    }*/
    
    public function getProdID(){
        $paymentInfo = $this->getInfoInstance();
        $order_number = $paymentInfo->getOrder()->getRealOrderId();
        
        return $order_number;
    }

    
    public function getCheckoutUrl()
    {

        
       /* error_log(" ID");
        error_log(print_r($this->getProdID(),true));

        error_log("MERCHANT ID");
        error_log(print_r($this->getMerchantId(),true));
        
        error_log("SECRET KEY");
        error_log(print_r($this->getSecretKey(),true));
        
        error_log("CANTIDAD");
        error_log(print_r($this->getAmount(),true));
        
        error_log("MONEDA");
        error_log(print_r($this->getCoin(),true));*/

    
        $url = '';
        require_once dirname(__FILE__) .'/lib/Nimble/base/NimbleAPI.php';
        $payment = array(
                'amount' => $this->getAmount(),
                'currency' => $this->getCoin(),
                'customerData' => $this->getProdID(),
                'paymentSuccessUrl' =>'http://local.magento19.com/checkout/onepage/success/?order='.$this->getProdID(), 
                'paymentErrorUrl' => 'http://local.magento19.com/payments/error'
        );

        $params = array(
                'clientId' => $this->getMerchantId(),
                'clientSecret' =>$this->getSecretKey(),
                'mode' => 'demo'
        );

        /* High Level call */
        try{
        $NimbleApi = new NimbleAPI($params);
        $p = new Payments();
        $response = $p->SendPaymentClient($NimbleApi, $payment);
        $url=$response["data"]["paymentUrl"];
//        if($response["result"]["info"]=='OK')
//            $this->afterSuccessOrder();
        }  catch (Exception $e){
            error_log($e->getMessage());
        }

        return $url;    
    }
    
   /* public function getTransactionUrl()
    { 
        if($this->isTestMode()) {
            $url = 'http://demo.nimblepayments.com/api/transactions/';
        }
        else {                    
            $url = 'https://www.nimblepayments.com/api/transactions/';
        }
        return $url;
    }*/
    
    public function getFormFields()
    {
        $postargs = "";
        $postargsAry = array();
        $fieldsArr = array();              
        $paymentInfo = $this->getInfoInstance();
        $shippingAddress = $this->getOrder()->getShippingAddress();
        $billingAddress = $this->getOrder()->getBillingAddress();    
        $additional_information = $paymentInfo->getAdditionalInformation();
        $MerchantId = $this->getMerchantId();
        $APIKey = $this->getSecretKey();
        $order_number = $paymentInfo->getOrder()->getRealOrderId();
        $order_amount = $this->getAmount();
        $fieldsArr = array(
            'Amount'=>urlencode($order_amount),
            'AllowRecurring'=>false,
            'RequestLang'=>(!empty($additional_information['lang']))?$additional_information['lang']:'',
            'Email'=>$billingAddress->getEmail(),
            'Phone'=>$billingAddress->getTelephone(),
            'FullName'=>$billingAddress->getFirstname().' '.$billingAddress->getLastname(),
            'IsPreAuth'=>false,
            'MerchantTrns'=>$order_number,
            'CustomerTrns'=>'Payment for your order #'.$order_number.'',
            'SourceCode'=>$this->getSourceCode(),
            'PaymentTimeOut'=>600,
            'AllowRecurring'=>false,
            'Tags'=>'Payment from website',
            'AllowTaxCard'=>false,
            //'ServiceId'=>4,
            'ActionUser'=>'NGD',
            'DisableIVR'=>true,
            'DisableCash'=>true,
            'DisableCard'=>false,
            'DisablePayAtHome'=>false
            );
            
        /*foreach($fieldsArr as $key => $val) {
            $postargsAry[] = $key.'='.$val;
        }
        $postargs = implode("&",$postargsAry);
        // Init request
        $ch = curl_init($this->getCreateOrderUrl());
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postargs);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $MerchantId.':'.$APIKey);
        if($this->isTestMode()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        }
        else {
            curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST,'TLSv1');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        $response = curl_exec($ch);
        curl_close($ch);
        try {
            if(is_object(json_decode($response))){
                  $resultObj=json_decode($response);
            }else{
                $result['error'] = Mage::helper('core')->__('Invalid response.');
                return $result;
            }
        } catch( Exception $e ) {
            $result['error'] = $e->getMessage();
            return $result;
        }
        if ($resultObj->ErrorCode==0){    //success when ErrorCode = 0
            $orderId = $resultObj->OrderCode;
         //   $this->_paymentUrl = $this->getCheckoutUrl().'?ref='.$orderId;
              $this->_paymentUrl = $this->getCheckoutUrl();
        }            
        
        else{
            $result['error'] = Mage::helper('core')->__($resultObj->ErrorText);
            return $result;
        }
        */
        $debugData = array(
            'request' => $fieldsArr
        );
        $this->_debug($debugData);   
        
        return $fieldsArr;
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
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

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
        //Mage::throwException(implode(',',$data));
        $result = parent::assignData($data); 
        return $result;
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
    
    public function afterSuccessOrder(/*$response*/)
    {
        error_log("afterSuccessOrder*****************************");
       /* $debugData = array(
            'response' => $response
        );
        $this->_debug($debugData);
        $infoModel = Mage::getModel('nimblepayments/info');
        $order = Mage::getModel('sales/order');        
        $order->loadByIncrementId($response->MerchantTrns);
        $paymentInst = $order->getPayment()->getMethodInstance();        
        $paymentInst->setStatus(self::STATUS_APPROVED)
                ->setLastTransId($response->TransactionId)
                ->setTransactionId($response->TransactionId)
                ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::TRANSACTION_TYPE_ID,$response->TransactionType->TransactionTypeId)
                ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::STATUS_ID,$response->StatusId)
                ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::SOURCE_CODE,$response->SourceCode)
                ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::ORDER_CODE,$response->Order->OrderCode)
                ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::TRANSACTION_TYPE_MESSAGE,$infoModel->_getTransactionTypeIdLabel($response->TransactionType->TransactionTypeId))
                ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::CREDIT_CARD_NUMBER,$infoModel->_getTransactionTypeIdLabel($response->CreditCard->Number))
                ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::ISSUING_BANK,$infoModel->_getTransactionTypeIdLabel($response->CreditCard->IssuingBank))
                ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::CREDIT_CARD_HOLDER,$infoModel->_getTransactionTypeIdLabel($response->CreditCard->CardHolderName))
                ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::CREDIT_CARD_TYPE,$infoModel->_getTransactionTypeIdLabel($response->CreditCard->CardType->Name));
                error_log("afterSuccessOrder1*****************************");

        $order->sendNewOrderEmail();                
        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice();
            
            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
        }
        $transaction = Mage::getModel('sales/order_payment_transaction');
        $transaction->setTxnId($response->TransactionId);
        $order->getPayment()->setAdditionalInformation(Bbva_NimblePayments_Model_Info::TRANSACTION_TYPE_ID,$response->TransactionType->TransactionTypeId)
                            ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::STATUS_ID,$response->StatusId)
                            ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::SOURCE_CODE,$response->SourceCode)
                            ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::ORDER_CODE,$response->Order->OrderCode)
                            ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::TRANSACTION_TYPE_MESSAGE,$infoModel->_getTransactionTypeIdLabel($response->TransactionType->TransactionTypeId))
                            ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::CREDIT_CARD_NUMBER,$infoModel->_getTransactionTypeIdLabel($response->CreditCard->Number))
                            ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::ISSUING_BANK,$infoModel->_getTransactionTypeIdLabel($response->CreditCard->IssuingBank))
                            ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::CREDIT_CARD_HOLDER,$infoModel->_getTransactionTypeIdLabel($response->CreditCard->CardHolderName))
                            ->setAdditionalInformation(Bbva_NimblePayments_Model_Info::CREDIT_CARD_TYPE,$infoModel->_getTransactionTypeIdLabel($response->CreditCard->CardType->Name));
        $transaction->setOrderPaymentObject($order->getPayment())
                    ->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
        $transaction->save();
       */
        $order_status = Mage::helper('core')->__('Payment is successful.');
         $paymentInfo = $this->getInfoInstance();
            $this->_order = Mage::getModel('sales/order')
                            ->loadByIncrementId($paymentInfo->getOrder()->getRealOrderId())
                            ->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING, $order_status)
                            ->save(); 
            
            
    }
}
