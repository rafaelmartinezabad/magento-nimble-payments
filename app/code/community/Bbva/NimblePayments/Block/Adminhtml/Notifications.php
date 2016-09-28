<?php
 
class Bbva_NimblePayments_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    public function _toHtml($className = "notification-global")
    {
        Mage::dispatchEvent('nimblepayments_notifications_before');
        $message = Mage::getSingleton('nimblepayments/notification')->getMessage();
        $html = "";
        if (!empty($message)) {
            $html = "<div class=\"notification-global\"><strong class=\"label\">".$this->__('Nimble Payments Message').":</strong> ".$message['message']." ";
            if ($message['type']=='plugins') {
                $html .= "<a href=\"".Mage::helper("adminhtml")->getUrl("adminhtml/system_config/edit/section/payment")."#payment_nimblepayments_checkout-head\">".$this->__('Activar')."</a>";
            } else {
                $html .= "<a href=\"".Bbva_NimblePayments_Block_Dashboard_Summary::getOauth3Url()."\">".$this->__('Authorize Magento')."</a>";
            }
            $html .= "</div>";
        }

        return $html;
    }

}