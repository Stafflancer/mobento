<?php
/**
 * The Magento Developer
 * http://themagentodeveloper.com
 *
 * @category   Sellry
 * @package    Sellry_OrderSync
 * @version    0.1.3
 */

class Sellry_OrderSync_Model_Mysql4_Inventory extends Mage_Core_Model_Mysql4_Abstract {
    protected function _construct() {
        $this->_init('ordersync/inventory', 'id');
    }
}