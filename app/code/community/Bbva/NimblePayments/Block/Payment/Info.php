<?php

class Bbva_NimblePayments_Block_Payment_Info extends Mage_Payment_Block_Info
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('nimblepayments/info/default.phtml');
    }

    /**
     * Prepare Nimble Payments-specific payment information
     *
     * @param Varien_Object|array $transport
     * return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();


        $vpcInfo = Mage::getModel('nimblepayments/info');

        if (!$this->getIsSecureMode()) {
            $info = $vpcInfo->getPaymentInfo($payment, true);
        } else {
            $info = $vpcInfo->getPublicPaymentInfo($payment, false);
        }
        return $transport->addData($info);
    }
}
