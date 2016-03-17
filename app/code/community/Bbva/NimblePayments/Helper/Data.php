<?php
class Bbva_NimblePayments_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function validateOrder($oid=0)
    {
        if(!$oid) return false;
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($oid);
        if(empty($order)) return false;
        return ($order->getStatus()==Bbva_NimblePayments_Model_Info::PAYMENTSTATUS_PENDING) ? true : false;
    }
}
