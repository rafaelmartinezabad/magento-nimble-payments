<?php
abstract class Bbva_NimblePayments_Controller_Abstract extends Mage_Core_Controller_Front_Action
{   
    /**
     * Redirect Block
     * need to be redeclared
     */
    protected $_redirectBlockType;

    protected function _expireAjax()
    {
        if (!$this->getCheckout()->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Nimble returns POST variables to this action
     */
    public function  successAction()
    { 
        $status = $this->_checkReturnedPost();
        $session = $this->getCheckout();

        $session->unsNimbleRealOrderId();
        $session->setQuoteId($session->getNimbleQuoteId(true));
        $session->getQuote()->setIsActive(false)->save();

        $order = Mage::getModel('sales/order');
        $order->load($this->getCheckout()->getLastOrderId());
        if($order->getId()) {
            $order->sendNewOrderEmail();
        }

        if ($status) {
            $this->_redirect('checkout/onepage/success');
        } else {
            $this->_redirect('*/*/failure');
        }
    }
}
