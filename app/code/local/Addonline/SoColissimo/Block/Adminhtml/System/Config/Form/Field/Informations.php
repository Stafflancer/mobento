<?php
/**
 * Addonline
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Addonline
 * @package     Addonline_SoColissimo
 * @copyright   Copyright (c) 2014 Addonline (http://www.addonline.fr)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */

/**
 * Addonline_SoColissimo
 *
 * @category    Addonline
 * @package     Addonline_SoColissimo
 * @copyright   Copyright (c) 2014 Addonline
 * @author 	    Addonline (http://www.addonline.fr)
 */
class Addonline_SoColissimo_Block_Adminhtml_System_Config_Form_Field_Informations extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Constructor
     */
    public function __()
    {
        $args = func_get_args();
        return false;
    }

    /* (non-PHPdoc)
     * @see Mage_Adminhtml_Block_System_Config_Form_Field::_getElementHtml()
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $version = Mage::getConfig()->getNode('modules/Addonline_SoColissimo/version');
        return 'Version: ' . $version;
    }
}
