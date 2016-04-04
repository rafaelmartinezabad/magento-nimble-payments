<?php

class Bbva_NimblePayments_Block_Dashboard extends Mage_Adminhtml_Block_Dashboard
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('nimblepaymentsadmin/dashboard/index.phtml');

    }
    
    protected function _prepareLayout()
    {
        $this->setChild('nimblepayments',
                $this->getLayout()->createBlock('nimblepayments/dashboard_summary')
        );
        return parent::_prepareLayout();
    }

}
