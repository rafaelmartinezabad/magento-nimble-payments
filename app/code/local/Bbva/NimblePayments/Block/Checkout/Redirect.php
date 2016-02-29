<?php
 
class Bbva_NimblePayments_Block_Checkout_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        $checkout = $this->getOrder()->getPayment()->getMethodInstance();
        $formFields = $checkout->getFormFields();
        
        if(!empty($formFields['error'])) {  
            Mage::getSingleton('core/session')->addError($formFields['error']);
            $url = Mage::getUrl('checkout/cart');
            Mage::app()->getResponse()->setRedirect($url);
            return;
        }
          
        $form = new Varien_Data_Form();        
        $form->setId('nimblepayments_checkout_checkout')
            ->setName('nimblepayments_checkout_checkout')
            ->setMethod('GET')
            ->setUseContainer(true);   
                     
        foreach ($checkout->getFormFields() as $field=>$value) {
            $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
        }   
        
        
        $form->setAction($checkout->getGatewayRedirectUrl());
        $html = '<html><body>';
        //$html.= $this->__('You will be redirected to nimble Payments website in a few seconds ...');
        //$html.= $form->toHtml();
        //$html.= '<script type="text/javascript">document.getElementById("nimblepayments_checkout_checkout").submit();</script>';
        $html.= '<a id="nimblepayments_checkout_checkout" href="'.$checkout->getGatewayRedirectUrl().'">You will be redirected to nimble Payments website in a few seconds ...</a>';
        $html.= '<script type="text/javascript">document.getElementById("nimblepayments_checkout_checkout").click();</script>';
        $html.= '</body></html>';
       //$html = str_replace('<div><input name="form_key" type="hidden" value="'.Mage::getSingleton('core/session')->getFormKey().'" /></div>','',$html);
       
        return $html;
    }
}
