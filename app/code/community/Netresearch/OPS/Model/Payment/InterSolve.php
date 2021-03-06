<?php
/**
 * Netresearch_OPS_Model_Payment_InterSolve
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_InterSolve
    extends Netresearch_OPS_Model_Payment_Abstract
{
    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** info source path */
    protected $_infoBlockType = 'ops/info_redirect';

    /** payment code */
    protected $_code = 'ops_interSolve';

    protected function getPayment()
    {
        $checkout = Mage::getSingleton('checkout/session');
        $payment = $checkout->getQuote()->getPayment();
        if (!$payment->getId()) {
            $payment = Mage::getModel('sales/order')->loadByIncrementId($checkout->getLastRealOrderId())->getPayment();
        }
        return $payment;
    }

    public function getOpsBrand($payment=null) {
        return trim($this->getPayment()->getAdditionalInformation('BRAND'));
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {

        $brand = '';
        if (is_object($data) && $data instanceof Varien_Object) {
            $brand = $data->getIntersolveBrand();
            $this->getHelper()->log(print_r($brand . ' VO', true));
        } elseif (is_array($data) && isset($data['intersolve_brand'])) {
            $brand = $data['intersolve_brand'];
            $this->getHelper()->log(print_r($brand . ' POST', true));
        }
        if (strlen(trim($brand)) === 0) {
            $brand = 'InterSolve';
        }
        $payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();
        $payment->setAdditionalInformation('BRAND', $brand);

        parent::assignData($data);
        return $this;
    }
}

