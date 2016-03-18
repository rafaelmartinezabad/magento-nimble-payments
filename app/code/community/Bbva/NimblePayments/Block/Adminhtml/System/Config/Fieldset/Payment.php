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
        $text_button = Mage::helper('core')->__('Signup now');
        return "<div><img style='height:4.9rem;' src='{$url_logo}' alt='Nimble logo'/></div><p>{$message} <a class='button button-primary' href='https://www.nimblepayments.com/private/registration?utm_source=Magento_Settings&utm_medium=Referral%20Partners&utm_campaign=Creacion-Cuenta&partner=magento' target='_blank'>{$text_button}</a></p><hr />";
    }
}
