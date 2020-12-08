<?php
class Wizkunde_ConfigurableBundle_Block_Catalog_Product_View_Type_Bundle_Option_Multi
    extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option_Multi
{
    public function _construct()
    {
        $this->setTemplate('configurablebundle/catalog/product/view/type/bundle/option/template.phtml');
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        if($this->optionIsConfigurable()) {
            return $this->_template;
        }

        return 'bundle/catalog/product/view/type/bundle/option/multi.phtml';
    }

    public function getSelectionTitlePrice($_selection, $includeContainer = true)
    {
        $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice($this->getProduct(), $_selection, 1);
        $this->setFormatProduct($_selection);
        return $_selection->getName();
    }

    /**
     * Determine if the option is configurable
     */
    public function optionIsConfigurable()
    {
        // Check if this is a CB
        $isConfigurable = false;

        foreach($this->getOption()->getSelections() as $_selection) {
            if($_selection->isConfigurable() == true) {
                $isConfigurable = true;
            }
        }

        return $isConfigurable;
    }

    /**
     * Determine the amount of configurables in this selection
     */
    public function countConfigurables()
    {
        $configurables = 0;

        foreach($this->getOption()->getSelections() as $_selection) {
            if($_selection->isConfigurable() == true) {
                $configurables++;
            }
        }

        return $configurables;
    }

    /**
     * Get the attribute codes used by the configurable
     *
     * @return array
     */
    public function getConfigurableAttributeCodes($configurableProduct = null)
    {
        $attributes = array();

        if($configurableProduct == null) {
            $configurableProduct = current($this->getConfigurables());
        }

        $configurableAttributes =  $configurableProduct->getTypeInstance()->getConfigurableAttributes();

        foreach($configurableAttributes as $attribute) {
            $attributes[] = $attribute->getProductAttribute()->getAttributeCode();
        }

        return $attributes;
    }

    /**
     * Return a array with all attributes matching a specific selection
     *
     * @return array
     */
    public function getAttributesToSelectionArray($configurable = null)
    {
        $selectionArray = array();

        $configurableAttributes = $this->getConfigurableAttributeCodes($configurable);

        $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $configurable);
        $hideOos = Mage::getStoreConfig('configurablebundle/configurable/remove_oos_products', Mage::app()->getStore()->getId());

        foreach($childProducts as $product) {
            $stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();

            if($hideOos == false || $stocklevel > 0) {
                $simpleAttributeData = array();
                foreach($configurableAttributes as $attribute) {
                    $simpleAttributeData[] = $product->getData($attribute);
                }

                // Create a imploded version of the attribute ids to the selection id, eg: 5_4 => 64
                // Which could resemble, if color == 5 and size == 4, then selection == 64
                foreach($this->getOption()->getSelections() as $selection) {
                    if($selection->getProductId() === $product->getId()) {
                        $selectionArray[implode('_', $simpleAttributeData)] = $selection->getSelectionId();
                    }
                }
            }
        }

        return $selectionArray;
    }

    /**
     * Get all the selection attributes to be able to determine all the information
     *
     * @return array
     */
    public function getSelectionAttributes()
    {
        return $this->getTypeInformation('simple');
    }

    public function getConfigurableData()
    {
        return $this->getTypeInformation('configurable');
    }

    public function getSwatchSettings()
    {
        return Mage::helper('configurablebundle/bundle')->getSwatchSettings();
    }

    public function getSwatchImages()
    {
        return Mage::helper('configurablebundle/bundle')->getSwatchImages($this->getOption());
    }

    /**
     * Get the module configurations
     * @return array
     */
    public function getUpdateStatus()
    {
        $updateStatus = array(
            'enable_swatches'       => (bool) Mage::getStoreConfig('configurablebundle/configurable/enable_swatches', Mage::app()->getStore()->getId()),
            'configurable_image'    => (bool) Mage::getStoreConfig('configurablebundle/configurable/configurable_image', Mage::app()->getStore()->getId()),
            'image'                 => (bool) Mage::getStoreConfig('configurablebundle/configurable/update_image', Mage::app()->getStore()->getId()),
            'name'                  => (bool) Mage::getStoreConfig('configurablebundle/configurable/update_name', Mage::app()->getStore()->getId()),
            'stock'                 => (bool) Mage::getStoreConfig('configurablebundle/configurable/update_stock', Mage::app()->getStore()->getId()),
            'description'           => (bool) Mage::getStoreConfig('configurablebundle/configurable/update_description', Mage::app()->getStore()->getId()),
            'show_oos'              => (bool) Mage::getStoreConfig('configurablebundle/configurable/remove_oos_products', Mage::app()->getStore()->getId()),
        );

        return $updateStatus;
    }
}