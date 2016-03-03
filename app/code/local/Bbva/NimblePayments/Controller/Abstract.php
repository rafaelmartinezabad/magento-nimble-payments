<?php
abstract class Bbva_NimblePayments_Controller_Abstract extends Mage_Core_Controller_Front_Action
{   
    protected function _expireAjax()
    {
        if (!$this->getCheckout()->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    /**
     * Redirect Block
     * need to be redeclared
     */
    protected $_redirectBlockType;

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
     * when customer select nimble payment method
     */
    public function redirectAction()
    {
        $status_new= 'pending_nimble';
        $session = $this->getCheckout();
        $session->setNimbleQuoteId($session->getQuoteId());
        $session->setNimbleRealOrderId($session->getLastRealOrderId());

        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());
        $order->addStatusToHistory($status_new, Mage::helper('core')->__('Customer was redirected to Nimble Payments.'));
        $order->save();

        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock($this->_redirectBlockType)
                ->setOrder($order)
                ->toHtml()
        );

        $session->unsQuoteId();
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

    /**
     * Display failure page if error
     *
     */
    public function failureAction()
    {
        if (!$this->getCheckout()->getNimbleErrorMessage()) {
            $this->norouteAction();
            return;
        }

        //$this->getCheckout()->clear();

        $this->loadLayout();
        $this->renderLayout();
    }    

}
