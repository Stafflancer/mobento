<?php
/**
 * Magento
 *  
 * NOTICE OF LICENSE
 *  
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *  
 * DISCLAIMER
 *  
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *  
 * @category  Nosto
 * @package   Nosto_Tagging
 * @author    Nosto Solutions Ltd <magento@nosto.com>
 * @copyright Copyright (c) 2013-2017 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class for Nosto related setups
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Setup extends Mage_Core_Helper_Abstract
{
    /**
     * Adds nosto_customer_reference as to the Customer entity
     *
     * @param $installer Mage_Eav_Model_Entity_Setup
     * @param $reset bool if set to true the existing nosto_customer_ref is removed
     *
     * @return void
     */
    public function addNostoCustomerReferenceEav(Mage_Eav_Model_Entity_Setup $installer, $reset = false)
    {
        $installer->startSetup();
        $entity = 'customer';
        $attributeExists = $installer->getAttribute(
            $entity,
            Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME
        );
        if ($attributeExists && $reset === true) {
            $installer->removeAttribute(
                $entity,
                Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME
            );
            $attributeExists = false;
        }
        if (!$attributeExists) {
            $attributeDefinition = array(
                "type" => "varchar",
                "label" => "Nosto Customer Reference",
                "input" => "text",
                "visible" => true,
                "required" => false,
                "unique" => true,
                "note" => "Unique customer reference for Nosto"
            );
            $installer->addAttribute(
                $entity,
                Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME,
                $attributeDefinition
            );
            /* @var Mage_Eav_Model_Config $eavConfig */
            $eavConfig = Mage::getSingleton("eav/config");
            $attribute = $eavConfig->getAttribute(
                $entity,
                Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME
            );

            $usedInForms = array("adminhtml_customer");
            $attribute->setData("used_in_forms", $usedInForms)
                ->setData("is_used_for_customer_segment", false)
                ->setData("is_system", 1)
                ->setData("is_user_defined", 0)
                ->setData("is_visible", 1)
                ->setData("sort_order", 100);

            $attribute->save();
        }
        $installer->endSetup();
    }

    /**
     * Changes the nosto_customer_reference frontend input type to text
     *
     * @param $installer Mage_Eav_Model_Entity_Setup
     * @return void
     * @suppress PhanTypeMismatchArgument
     */
    public function alterCustomerReferenceInputType(Mage_Eav_Model_Entity_Setup $installer)
    {
        $installer->updateAttribute(
            'customer',
            Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME,
            array(
                'frontend_input' => 'text',
            )
        );
    }

    /**
     * Adds restore cart hash to the customer table
     *
     * @param $installer Mage_Eav_Model_Entity_Setup
     * @return void
     * @suppress PhanTypeMismatchArgument
     */
    public function addRestoreCartHash(Mage_Eav_Model_Entity_Setup $installer)
    {
        $installer->startSetup();
        $installer->getConnection()
            ->addColumn(
                $installer->getTable('nosto_tagging/customer'),
                Nosto_Tagging_Helper_Data::NOSTO_TAGGING_RESTORE_CART_ATTRIBUTE,
                array(
                    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                    'length' => Nosto_Tagging_Helper_Data::NOSTO_TAGGING_RESTORE_CART_ATTRIBUTE_LENGTH,
                    'nullable' => true,
                    'comment' => 'A field containing the restore cart hash',
                )
            );
        $installer->endSetup();
    }
}
