<?php


class Bbva_NimblePaymentsCheckout_Block_Link extends Mage_Checkout_Block_Onepage_Link
{

    public function addFasterPageCheckoutLink()
    {
        error_log($this->helper('nimblepaymentscheckout')->fasterpageCheckoutEnabled());
        if (!$this->helper('nimblepaymentscheckout')->fasterpageCheckoutEnabled()) {
            error_log("entra aqui en add");
            return $this;
        }

        $parentBlock = $this->getParentBlock();
        if ($parentBlock && Mage::helper('core')->isModuleOutputEnabled('Bbva_NimblePaymentsCheckout')) {
            $text = $this->__('Faster Checkout');
            $parentBlock->addLink(
                $text,
                'checkout/fasterpage',
                $text,
                true,
                array('_secure' => true),
                60,
                null,
                'class="top-link-checkout"'
            );
        }
        return $this;
    }

    function isPossibleFasterPageCheckout()
    {
        return $this->helper('nimblepaymentscheckout')->fasterPageCheckoutEnabled();
    }

    function getCheckoutUrl()
    {
        return $this->getUrl('checkout/fasterpage', array('_secure'=>true));
    }
}
