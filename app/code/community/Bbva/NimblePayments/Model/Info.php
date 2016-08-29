<?php 
class Bbva_NimblePayments_Model_Info
{
    /**
     * Cross-models public exchange keys
     *
     * @var string
     */
    const TRANSACTION_ID        = 'TransactionId';
    const CREDIT_CARD_PAN = 'maskedPan';
    const CREDIT_CARD_TYPE = 'cardBrand';
    const CHANGE_ADDRESS = 'changeAddress';

    /**
     * All payment information map
     *
     * @var array
     */
    protected $_paymentMap = array(
        self::TRANSACTION_ID        => 'np_transaction_id',
        self::CREDIT_CARD_PAN          => 'maskedPan',
        self::CREDIT_CARD_TYPE => 'cardBrand'
    );
    
    /**
     * Map of payment information available to customer
     *
     * @var array
     */
    protected $_paymentPublicMap = array(
        self::CREDIT_CARD_PAN          => 'maskedPan',
        self::CREDIT_CARD_TYPE => 'cardBrand'
    );

    /**
     * Map of additional data payment information available
     *
     * @var array
     */
    protected $_paymentAditionalFields = array(
        self::CHANGE_ADDRESS => 'changeAddress'
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
     * Public additional data payment info getter
     *
     * @param Mage_Payment_Model_Info $payment
     * @param bool $labelValuesOnly
     * @return array
     */
    public function getAdditionalFields(Mage_Payment_Model_Info $payment, $labelValuesOnly = false)
    {
        $result = $this->_getFullInfo(array_values($this->_paymentAditionalFields), $payment, $labelValuesOnly);
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
        $result = $this->_getFullInfo(array_values($this->_paymentPublicMap), $payment, $labelValuesOnly);
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
            case 'np_transaction_id':
                return Mage::helper('payment')->__('Nimble Payments Transaction ID');
            case 'maskedPan':
                return Mage::helper('payment')->__('Card Pan');
            case 'cardBrand':
                return Mage::helper('payment')->__('Card Type');
            case 'changeAddress':
                return Mage::helper('payment')->__('isChangeShippingAddress');
        }
        return '';
    }

}
