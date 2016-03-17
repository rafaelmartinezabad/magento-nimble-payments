<?php

class Bbva_NimblePayments_Block_Info extends Mage_Payment_Block_Info_Cc
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('nimblepayments/info.phtml');
    }

    public function toPdf()
    {
        $this->setTemplate('nimblepayments/pdf/info.phtml');
        return $this->toHtml();
    }

}
