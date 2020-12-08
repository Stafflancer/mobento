<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Block_Adminhtml_Pdf
//    extends Mage_Adminhtml_Block_Widget_Form_Container
extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'ugiftcert';
        $this->_controller = 'adminhtml_pdf';
// todo move bellow code to editing section of templates
//        $this->_updateButton('save', 'label', Mage::helper('ugiftcert')->__('Save PDF Settings'));
//        $this->_removeButton('reset')->_removeButton('back');
    }

    public function getHeaderText()
    {
        return Mage::helper('ugiftcert')->__('PDF Printout Settings');
    }
}
