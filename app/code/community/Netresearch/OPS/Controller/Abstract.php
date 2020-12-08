<?php
/**
 * Netresearch_OPS_Controller_Abstract
 * 
 * @package   
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de> 
 * @author    André Herrn <andre.herrn@netresearch.de> 
 * @license   OSL 3.0
 */
class Netresearch_OPS_Controller_Abstract extends Mage_Core_Controller_Front_Action
{
    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function getConfig()
    {
        return Mage::getModel('ops/config');
    }

    /**
     * Return order instance loaded by increment id'
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder($quoteId=null)
    {
        if (empty($this->_order)) {
            if (is_null($quoteId)) {
                $quoteId = $this->getRequest()->getParam('orderID');
            }
            $devPrefix = $this->getConfig()->getConfigData('devprefix');
            if ($devPrefix == substr($quoteId, 0, strlen($devPrefix))) {
                $quoteId = substr($quoteId, strlen($devPrefix));
            }
            $this->_order = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('quote_id', $quoteId)
                ->getFirstItem();
        }
        return $this->_order;
    }

    /**
     * Get singleton with Checkout by OPS Api
     *
     * @return Netresearch_OPS_Model_Payment_Abstract
     */
    protected function _getApi()
    {
        if (!is_null($this->getRequest()->getParam('orderID'))):
            return $this->_getOrder()->getPayment()->getMethodInstance();
        else:
            return Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethodInstance();
        endif;
    }

    /**
     * get payment helper
     * 
     * @return Netresearch_OPS_Helper_Payment
     */
    protected function getPaymentHelper()
    {
        return Mage::helper('ops/payment');
    }
    
    /**
     * get direct link helper
     * 
     * @return Netresearch_OPS_Helper_Payment
     */
    protected function getDirectlinkHelper()
    {
        return Mage::helper('ops/directlink');
    }

    /**
     * Validation of incoming OPS data
     *
     * @return bool
     */
    protected function _validateOPSData()
    {
        $params = $this->getRequest()->getParams();

        $secureKey = $this->_getApi()->getConfig()->getShaInCode();
        $secureSet = $this->getPaymentHelper()->getSHAInSet($params, $secureKey);

        $helper = Mage::helper('ops');
        $helper->log($helper->__("Incoming Ogone Feedback\n\nRequest Path: %s\nParams: %s\n",
            $this->getRequest()->getPathInfo(),
            serialize($this->getRequest()->getParams())
        ));
        
        if (Mage::helper('ops/payment')->shaCryptValidation($secureSet, $params['SHASIGN']) !== true) {
            $this->_getCheckout()->addError($this->__('Hash is not valid'));
            return false;
        }

        $order = $this->_getOrder();
        if (!$order->getId()){
            $this->_getCheckout()->addError($this->__('Order is not valid'));
            return false;
        }

        return true;
    }

    public function isJsonRequested($params)
    {
        if (array_key_exists('RESPONSEFORMAT', $params) && $params['RESPONSEFORMAT'] == 'JSON') {
                return true;
        }
        return false;
    }
}
