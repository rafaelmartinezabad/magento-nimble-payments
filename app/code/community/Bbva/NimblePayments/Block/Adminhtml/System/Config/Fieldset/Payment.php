<?php
class Bbva_NimblePayments_Block_Adminhtml_System_Config_Fieldset_Payment
        extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return header comment part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {   

        $url_logo = $this->getSkinUrl('images/nimblepayments/nimble_bbva.svg');
        $message = Mage::helper('core')->__('Need an <strong>Nimble Payments</strong> account?.');
        $text_button = Mage::helper('core')->__('Signup now.');
        $text_button2 = Mage::helper('core')->__('Already registered.');
        $url_nimble = $this->get_gateway_url();
        $env = substr(split("://", $url_nimble)[1], 0, 3);
        return "<div><img style='height:4.9rem;' src='{$url_logo}' alt='Nimble logo'/></div><p>{$message} <a class='button button-primary' href='https://$env.nimblepayments.com/private/registration?utm_source=Magento_Settings&utm_medium=Referral%20Partners&utm_campaign=Creacion-Cuenta&partner=magento' target='_blank'>{$text_button}</a> "
        . "&nbsp;&nbsp;<a style='cursor:pointer;' onclick=\"window.open('{$url_nimble}', '', 'width=800, height=578')\">{$text_button2}</a></p><hr />";
    }
        
    static function get_gateway_url(){
        
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        
        $platform = 'Magento'; //TODO write real name
        $storeName = Mage::app()->getWebsite(true)->getDefaultStore()->getFrontendName();
        $storeURL = Mage::app()->getWebsite(true)->getDefaultStore()->getUrl();
        $redirectURL = Mage::app()->getWebsite(true)->getDefaultStore()->getUrl('', array('_direct'=>'nimblepayments/oauth3'));
        
        return NimbleAPI::getGatewayUrl($platform, $storeName, $storeURL, $redirectURL);
    }
}
