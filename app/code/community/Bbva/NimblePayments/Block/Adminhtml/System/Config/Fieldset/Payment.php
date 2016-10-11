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

    /**
     * Return footer html for fieldset
     * Add extra tooltip comments to elements
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getFooterHtml($element)
    {
        $tooltipsExist = false;
        $html .= '</tbody></table>';
        if(Mage::getModel('nimblepayments/checkout')->showAuthorize()) {
            if (Mage::getModel('nimblepayments/checkout')->is3leggedToken()) {
                $html .= $this->getUnlinkFooter();
            } else {
                $html .= $this->getAuthorizeFooter();
            }
        }
        $html .= '</fieldset>' . $this->_getExtraJs($element, $tooltipsExist);
        if ($element->getIsNested()) {
            $html .= '</div></td></tr>';
        } else {
            $html .= '</div>';
        }
        return $html;
    }

    private function getUnlinkFooter() {
        $footer = '<hr/>
            <p id="nimble-unlink-footer-block" class="nimble-config-footer-block">'.
                '<label>'.$this->__('Store linked to Nimble Payments'). /* tr022 */ '</label></br>'.
                '<a class="button button-primary" href='.Mage::getUrl('nimblepayments/oauth3/unlink', array('key' => Mage::app()->getRequest()->getParam('key'))).'>'.$this->__('Disassociate').'</a>
            </p>';
        return $footer;
    }

    private function getAuthorizeFooter() {
        $footer = '<hr/>
            <p id="nimble-authorize-footer-block-part-1" class="nimble-config-footer-block">'.
                '<label>'.$this->__('You can do everything in Magento site'). /* tr024 */ ':</label></br>'.
                '<label>'.$this->__('manage your purchases, check your account transactions, request a refund...'). /* tr025 */'</label></br>'.
            '</p>'.
            '<hr id="inner-authorize">'.
            '<p id="nimble-authorize-footer-block-part-2" class="nimble-config-footer-block">'.
                '<label>'.$this->__('log in and allow us to access your information'). /* tr026 */'</label></br></br>'.
                '<a class="button button-primary" href='.$this->getOauth3Url().'>'.$this->__('Allow Magento').'</a>'.
            '</p>';
        return $footer;
    }
        
    static function get_gateway_url(){
        
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        
        $platform = 'Magento'; //TODO write real name
        $storeName = Mage::app()->getWebsite(true)->getDefaultStore()->getFrontendName();
        $storeURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $redirectURL = $storeURL.'nimblepayments/oauth3';
        
        return NimbleAPI::getGatewayUrl($platform, $storeName, $storeURL, $redirectURL);
    }

    private function getOauth3Url(){
        return Mage::getSingleton('Bbva_NimblePayments_Block_Dashboard_Summary')->getOauth3Url();
    }
}
