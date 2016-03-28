<?php

class Bbva_NimblePayments_Model_Checkout extends Mage_Payment_Model_Method_Abstract
{
    
    protected $_code  = 'nimblepayments_checkout';

    protected $_isGateway               = false;
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
    
    public function getProdID(){
        $paymentInfo = $this->getInfoInstance();
        $order_number = $paymentInfo->getOrder()->getRealOrderId();
        
        return $order_number;
    }

    
    public function getCheckoutUrl()
    {
        $url = '';
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        
        $order_id = $this->getProdID();
        $key = Mage::getSingleton('adminhtml/url')->getSecretKey('nimblepayments', $order_id);
        $payment = array(
                'amount' => $this->getAmount(),
                'currency' => $this->getCoin(),
                'customerData' => $order_id,
                'paymentSuccessUrl' => Mage::getUrl('checkout/onepage/success', array('order' => $order_id, 'key' => $key)),
                'paymentErrorUrl' =>Mage::getUrl('nimblepayments/checkout/failure')
        );
        
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
            }else {
                $url=$payment["paymentErrorUrl"].'?error=false';
            }
            
        }  catch (Exception $e){
            $url=$payment["paymentErrorUrl"].'?connection=false';
            
        }
        
        return $url;    
    }
    
    public function getParams(){
        
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        
        $params = array(
                'clientId' => $this->getMerchantId(),
                'clientSecret' =>$this->getSecretKey(),
                'mode' => NimbleAPIConfig::MODE
        );
        return $params;
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
    
    /**
     * Retrieve payment method title
     *
     * @return string
     */
    public function getTitle()
    {
        return Mage::helper('core')->__('Card payment');
    }
    
}
