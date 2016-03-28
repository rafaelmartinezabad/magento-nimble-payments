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
        require_once Mage::getBaseDir() . '/lib/Nimble/base/NimbleAPI.php';
        require_once Mage::getBaseDir() . '/lib/Nimble/api/NimbleAPIPayments.php';
        
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
}