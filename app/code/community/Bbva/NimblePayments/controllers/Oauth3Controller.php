<?php
/**
 * Created by PhpStorm.
 * User: acasado
 * Date: 4/03/16
 * Time: 12:37
 */
class Bbva_NimblePayments_Oauth3Controller extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        if (Mage::app()->getRequest()->has('code')){
            $this->authorize();
        } else if (Mage::app()->getRequest()->has('ticket')){
            $this->refund();
        }
    }
    
    public function authorize(){
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        
        $params   =   array();
        $code     =   Mage::app()->getRequest()->getParam('code');
        $obj      =   new Bbva_NimblePayments_Model_Checkout();
        $params_1 =   $obj->getParams();
        $params_2 =   array(
                        'authType' => '3legged',
                        'oauth_code' => $code
                      );
        
        $params = array(
          'clientId'      =>  $params_1['clientId'],
          'clientSecret'  =>  $params_1['clientSecret'], 
          'mode'          =>  $params_1['mode'],
          'authType'      =>  $params_2['authType'],
          'oauth_code'    =>  $params_2['oauth_code'],    
        ); 
        
        try {
            $nimble_api = new NimbleAPI($params);
            $options = array(
              'token' => $nimble_api->authorization->getAccessToken(),
              'refreshToken' => $nimble_api->authorization->getRefreshToken()
            );         
            //guardar el nombre de reflesh token y refles en BD.
            $Switch = new Mage_Core_Model_Config();
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('core')->__('Autentifiacion de 3 pasos correcta'));
            $Switch->saveConfig('payment/nimblepayments_checkout/token', $options['token'], 'default', 0)
                   ->saveConfig('payment/nimblepayments_checkout/refreshToken', $options['refreshToken'], 'default', 0);
        } catch (Exception $e) {
                    Mage::getSingleton('core/session')->addError(Mage::helper('core')->__('Autentifiacion de 3 pasos incorrecta'));
          }

        $this->loadLayout(array('default'));
        $this->renderLayout();
    }
    
    public function refund(){
        $serialized_ticket = Mage::getStoreConfig('payment/nimblepayments_checkout/ticket');
        $otp_info = unserialize($serialized_ticket);
        $ticket = Mage::app()->getRequest()->getParam('ticket');
        $result = Mage::app()->getRequest()->getParam('result');
        
        if ($ticket == $otp_info['ticket']) {
            $block = $this->getLayout()->createBlock(
                'Mage_Core_Block_Template',
                'Oauth3',
                array('template' => 'nimblepayments/ticket.phtml')
                )
                ->setData('url', $otp_info['REQUEST_URI'])
                    ->setData('ticket', $otp_info['ticket'])
                    ->setData('result', $result)
                    ->setData('form_key', $otp_info['form_key'])
                    ->setData('creditmemo', $otp_info['creditmemo']);

            $this->loadLayout(array('default'));
            $this->getLayout()->getBlock('content')->append($block);
            $this->renderLayout();
        } else {
            $block = $this->getLayout()->createBlock(
                'Mage_Core_Block_Template',
                'Oauth3',
                array('template' => 'nimblepayments/ticket.phtml')
                )
                ->setData('error', 'invalid_token');

            $this->loadLayout(array('default'));
            $this->getLayout()->getBlock('content')->append($block);
            $this->renderLayout();
        }
        
    }
}