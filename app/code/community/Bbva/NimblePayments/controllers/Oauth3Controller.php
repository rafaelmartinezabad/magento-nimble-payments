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
        $code = Mage::app()->getRequest()->getParam('code');
        //$url = Mage::getSingleton('adminhtml/url')->getUrl('index.php/admin/system_config/index');
        //$this->_redirectUrl($url);
        $this->loadLayout(array('default'));
        $this->renderLayout();
    }
}