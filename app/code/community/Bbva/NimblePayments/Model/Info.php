<?php 
class Bbva_NimblePayments_Model_Info
{
    /**
     * Cross-models public exchange keys
     *
     * @var string
     */
    const MERCHANT_ID          = 'MerchantId';
    const STATUS_ID            = 'StatusId';    
    const SOURCE_CODE            = 'SourceCode';
    const TRANSACTION_ID        = 'TransactionId';
    const MERCH_TXN_REF         = 'MerchantTrns';
    const ORDER_CODE              = 'OrderCode';
    const TRANSACTION_TYPE_ID      = 'TransactionTypeId';
    const TRANSACTION_TYPE_MESSAGE = 'TransactionTypeMessage';
    const CREDIT_CARD_NUMBER = 'CcNumber';
    const ISSUING_BANK = 'IssuingBank';
    const CREDIT_CARD_HOLDER = 'CardHolderName';
    const CREDIT_CARD_PAN = 'CardPan';
    const CREDIT_CARD_TYPE = 'CardType';

    /**
     * All payment information map
     *
     * @var array
     */
    protected $_paymentMap = array(
        self::MERCHANT_ID          => 'MerchantId',
        self::STATUS_ID            => 'StatusId',
        self::SOURCE_CODE            => 'SourceCode',
        self::TRANSACTION_ID        => 'np_transaction_id',
        self::MERCH_TXN_REF         => 'MerchantTrns',
        self::ORDER_CODE              => 'OrderCode',
        self::TRANSACTION_TYPE_ID      => 'TransactionTypeId',
        self::TRANSACTION_TYPE_MESSAGE      => 'TransactionTypeMessage',
        self::CREDIT_CARD_NUMBER => 'CcNumber',
        self::ISSUING_BANK => 'IssuingBank',
        self::CREDIT_CARD_HOLDER => 'CardHolderName',
        self::CREDIT_CARD_TYPE => 'CardType'
    );
    
        /**
     * All payment information map
     *
     * @var array
     */
    protected $_paymentMapPublic = array(
        self::CREDIT_CARD_PAN          => 'CardPan',
        self::CREDIT_CARD_TYPE => 'CardType'
    );
    
    /**
     * Nimble payment status possible values
     *
     * @var string
     */
    const PAYMENTSTATUS_NONE         = 'none';
    const PAYMENTSTATUS_PENDING      = 'pending';
    const PAYMENTSTATUS_ACCEPTED     = 'accepted';
    const PAYMENTSTATUS_REJECTED     = 'rejected';
    const PAYMENTSTATUS_REVIEWED     = 'reviewed';
    const PAYMENTSTATUS_NOTCHECKED   = 'not_checked';
    const PAYMENTSTATUS_SYSREJECT    = 'system_rejected';

    /**
     * Map of payment information available to customer
     *
     * @var array
     */
    protected $_paymentPublicMap = array(
        //'',
    );

    /**
     * Rendered payment map cache
     *
     * @var array
     */
    protected $_paymentMapFull = array();

    /**
     * All available payment info getter
     *
     * @param Mage_Payment_Model_Info $payment
     * @param bool $labelValuesOnly
     * @return array
     */
    public function getPaymentInfo(Mage_Payment_Model_Info $payment, $labelValuesOnly = false)
    {
        // collect Nimble-specific info
        $result = $this->_getFullInfo(array_values($this->_paymentMap), $payment, $labelValuesOnly);

        return $result;
    }

    /**
     * Public payment info getter
     *
     * @param Mage_Payment_Model_Info $payment
     * @param bool $labelValuesOnly
     * @return array
     */
    public function getPublicPaymentInfo(Mage_Payment_Model_Info $payment, $labelValuesOnly = false)
    {
        $result = $this->_getFullInfo(array_values($this->_paymentMapPublic), $payment, $labelValuesOnly);
        return $result;
    }

    /**
     * Grab data from source and map it into payment
     *
     * @param array|Varien_Object|callback $from
     * @param Mage_Payment_Model_Info $payment
     */
    public function importToPayment($from, Mage_Payment_Model_Info $payment)
    {
        $fullMap = array_merge($this->_paymentMap, $this->_systemMap);
        if (is_object($from)) {
            $from = array($from, 'getDataUsingMethod');
        }
        Varien_Object_Mapper::accumulateByMap($from, array($payment, 'setAdditionalInformation'), $fullMap);
    }

    /**
     * Grab data from payment and map it into target
     *
     * @param Mage_Payment_Model_Info $payment
     * @param array|Varien_Object|callback $to
     * @param array $map
     * @return array|Varien_Object
     */
    public function &exportFromPayment(Mage_Payment_Model_Info $payment, $to, array $map = null)
    {
        $fullMap = array_merge($this->_paymentMap, $this->_systemMap);
        Varien_Object_Mapper::accumulateByMap(array($payment, 'getAdditionalInformation'), $to,
            $map ? $map : array_flip($fullMap)
        );
        return $to;
    }


    /**
     * Render info item
     *
     * @param array $keys
     * @param Mage_Payment_Model_Info $payment
     * @param bool $labelValuesOnly
     */
    protected function _getFullInfo(array $keys, Mage_Payment_Model_Info $payment, $labelValuesOnly)
    {
        $result = array();
        foreach ($keys as $key) {
            if (!isset($this->_paymentMapFull[$key])) {
                $this->_paymentMapFull[$key] = array();
            }
            if (!isset($this->_paymentMapFull[$key]['label'])) {
                if (!$payment->hasAdditionalInformation($key)) {
                    $this->_paymentMapFull[$key]['label'] = false;
                    $this->_paymentMapFull[$key]['value'] = false;
                } else {
                    $value = $payment->getAdditionalInformation($key);
                    $this->_paymentMapFull[$key]['label'] = $this->_getLabel($key);
                    $this->_paymentMapFull[$key]['value'] = $value;
                }
            }
            if (!empty($this->_paymentMapFull[$key]['value'])) {
                if ($labelValuesOnly) {
                    $result[$this->_paymentMapFull[$key]['label']] = $this->_paymentMapFull[$key]['value'];
                } else {
                    $result[$key] = $this->_paymentMapFull[$key];
                }
            }
        }
        return $result;
    }

    /**
     * Render info item labels
     *
     * @param string $key
     */
    public function _getLabel($key)
    {
        switch ($key) {
            case 'MerchantId':
                return Mage::helper('payment')->__('Merchant Id');
            case 'StatusId':
                return Mage::helper('payment')->__('Status Id');
            case 'SourceCode':
                return Mage::helper('payment')->__('Source Code');
            case 'np_transaction_id':
                return Mage::helper('payment')->__('Nimble Payments Transaction ID');
            case 'MerchantTrns':
                return Mage::helper('payment')->__('Merchant Transaction Reference');
            case 'OrderCode':
                return Mage::helper('payment')->__('Order Code');
            case 'TransactionTypeId':
                return Mage::helper('payment')->__('Transaction Type Id');
            case 'TransactionTypeMessage':
                return Mage::helper('payment')->__('Transaction Type Message');
            case 'CcNumber':
                return Mage::helper('payment')->__('Credit Card Number');
            case 'IssuingBank':
                return Mage::helper('payment')->__('Issuing Bank');
            case 'CardHolderName':
                return Mage::helper('payment')->__('Card Holder Name');
            case 'CardPan':
                return Mage::helper('payment')->__('Card Pan');
            case 'CardType':
                return Mage::helper('payment')->__('Card Type');
        }
        return '';
    }

}
