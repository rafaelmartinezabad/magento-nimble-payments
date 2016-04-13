<?php


class Bbva_NimblePaymentsCheckout_Block_Fasterpage extends Mage_Checkout_Block_Onepage
{
    public function getActiveStep()
    {
        foreach ($this->getSteps() as $step_code => $step){
            if (!isset($step['complete']) || $step['complete'] == false)
                return $step_code;
        }
        return parent::getActiveStep();
    }
}
