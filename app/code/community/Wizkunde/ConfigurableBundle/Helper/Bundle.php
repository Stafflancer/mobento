<?php

/**
 * Helper class for transposing bundle information
 *
 * Class Wizkunde_ConfigurableBundle_Helper_Bundle
 */
class Wizkunde_ConfigurableBundle_Helper_Bundle extends Mage_Core_Helper_Abstract
{

    protected $CONFIG_REMOVE_OUT_OF_STOCK = 'configurablebundle/configurable/remove_oos_products';

    public function getSwatchEnabled()
    {
        return (bool) Mage::getStoreConfig('configurablebundle/configurable/enable_swatches', Mage::app()->getStore()->getId());
    }

    public function getSwatchSettings()
    {
        if($this->getSwatchEnabled()) {
            $_dimHelper = Mage::helper('configurableswatches/swatchdimensions');

            return array(
                'inner_width' => $_dimHelper->getInnerWidth(Mage_ConfigurableSwatches_Helper_Swatchdimensions::AREA_DETAIL),
                'inner_height' => $_dimHelper->getInnerHeight(Mage_ConfigurableSwatches_Helper_Swatchdimensions::AREA_DETAIL),
                'outer_width' => $_dimHelper->getOuterWidth(Mage_ConfigurableSwatches_Helper_Swatchdimensions::AREA_DETAIL),
                'outer_height' => $_dimHelper->getOuterHeight(Mage_ConfigurableSwatches_Helper_Swatchdimensions::AREA_DETAIL)
            );
        }

        return array();
    }

    public function getSwatchImages($bundleOption)
    {
        $images = array();

        if($this->getSwatchEnabled()) {
            $_swatchHelper = Mage::helper('configurableswatches/data');
            $swatchSettings = $this->getSwatchSettings();

            foreach($_swatchHelper->getSwatchAttributeIds() as $attributeId) {
                $images[$attributeId] = array();

                $attr = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);

                if ($attr->usesSource()) {
                    $options = $attr->getSource()->getAllOptions(false);

                    foreach($options as $option) {
                        foreach($bundleOption->getSelections() as $selection) {
                            $_swatchUrl = Mage::helper('configurableswatches/productimg')
                                ->getGlobalSwatchUrl(
                                    Mage::getModel('catalog/product'),
                                    $option['label'],
                                    $swatchSettings['inner_width'],
                                    $swatchSettings['inner_height']
                                );

                            if (!empty($_swatchUrl)) {
                                $images[$attributeId][$option['value']] = $_swatchUrl;
                            }
                        }
                    }
                }
            }
        }

        return $images;
    }

    /**
     * @param Mage_Catalog_Model_Product $bundleProduct
     *
     * @return array
     */
    public function getProductToSelectionIds($bundleId) 
    {
        $bundleProduct = Mage::getModel('catalog/product')->load($bundleId);
        $selectionCollection = $bundleProduct->getTypeInstance(true)->getSelectionsCollection(
            $bundleProduct->getTypeInstance(true)->getOptionsIds($bundleProduct), $bundleProduct
        );

        $bundledItems = array();
        foreach($selectionCollection as $option)
        {
            $bundledItems[$option->product_id] = $option->selection_id;
        }

        return $bundledItems;
    }

    /**
     * @param Mage_Catalog_Model_Product $bundleProduct
     *
     * @return array
     */
    public function getSelectionToProductIds($bundleId) 
    {
        return array_flip($this->getProductSelectionIds($bundleId));
    }

    /**
     * Get the session
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get the cart
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    public function resolveBundleSelectionId($bundleId, $selectionId)
    {
        $product = Mage::getModel('catalog/product')->load($bundleId);

        $selections = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)
                ->getOptionsIds($product), $product
            );

        foreach($selections as $selection){
            if($selection->getSelectionId() == $selectionId) {
                return $selection->getProductId();
            }
        }
    }

    /**
     * We need to make sure the proper super attributes are selected in the buy request
     */
    public function formatConfigurableAttributesInBuyRequest($product)
    {
        $currentAttributes = $this->_getRequest()->getParam('super_attribute');

        foreach($this->_getRequest()->getParam('bundle_option') as $optionIterator => $bundleOption) {
            $realProduct = $this->resolveBundleSelectionId($product->getEntityId(), $bundleOption);
            if(isset($currentAttributes[$optionIterator])) {
                $currentAttributes[$realProduct] = $currentAttributes[$optionIterator];

                // Do not unset option 1 if it happens to be product 1
                if($optionIterator != $realProduct) {
                    unset($currentAttributes[$optionIterator]);
                }
            }
        }

        $this->_getRequest()->setParam('super_attribute', $currentAttributes);

        return $this->_getRequest()->getParams();
    }

    public function hasProductOptions($product)
    {
        $fullProduct = Mage::getModel('catalog/product')->load($product->getId());

        return (bool) (is_array($fullProduct->getOptions()) && count($fullProduct->getOptions()) > 0);
    }

    public function getProductOptions($product)
    {
        $blockOption = Mage::app()->getLayout()->createBlock("Mage_Catalog_Block_Product_View_Options");
        $blockOption->addOptionRenderer("default", "catalog/product_view_options_type_default", "catalog/product/view/options/type/default.phtml");
        $blockOption->addOptionRenderer("text", "catalog/product_view_options_type_text", "catalog/product/view/options/type/text.phtml");
        $blockOption->addOptionRenderer("file", "catalog/product_view_options_type_file", "catalog/product/view/options/type/file.phtml");
        $blockOption->addOptionRenderer("select", "catalog/product_view_options_type_select", "catalog/product/view/options/type/select.phtml");
        $blockOption->addOptionRenderer("date", "catalog/product_view_options_type_date", "catalog/product/view/options/type/date.phtml");


        $blockOptionsHtml = null;

        if($product->getTypeId()=="simple"||$product->getTypeId()=="virtual"||$product->getTypeId()=="configurable")
        {
            $fullProduct = Mage::getModel('catalog/product')->load($product->getId());

            $blockOption->setProduct($fullProduct);

            if(is_array($fullProduct->getOptions()))
            {
                foreach ($fullProduct->getOptions() as $o)
                {
                    $blockOptionsHtml .= $blockOption->getOptionHtml($o);
                };
            }
        }

        if($product->getTypeId() == 'simple' || $product->getTypeId() == 'configurable') {
            $blockOptionsHtml = str_replace('options[', 'bundle_simple_custom_options[' . $fullProduct->getEntityId() . '][options][', $blockOptionsHtml);
        }

        return $blockOptionsHtml;
    }
    
    public function addToCart($product, $response)
    {
        /**
         * Needed to make sure the proper super attributes are selected when adding a bundle to the cart
         */
        $this->formatConfigurableAttributesInBuyRequest($product);

        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $related = $this->_getRequest()->getParam('related_product') != '' ? $this->_getRequest()->getParam('related_product') : array();

            /**
             * Check product availability
             */
            if (!$product) {
                return false;
            }

            /**
             * Add to cart
             */
            $this->getCart()->addProduct($product, $this->_getRequest()->getParams());

            /**
             * Add related products
             */
            if (!empty($related)) {
                $this->getCart()->addProductsByIds(explode(',', $related));
            }

            $this->getCart()->save();

            $this->getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent(
                'checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->_getRequest(), 'response' => $response)
            );

            if (!$this->getSession()->getNoCartRedirect(true)) {
                if (!$this->getCart()->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->getSession()->addSuccess($message);
                }

                return true;
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->getSession()->getUseNotice(true)) {
                $this->getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            return false;
        } catch (Exception $e) {
            $this->getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            return true;
        }
    }


    /**
     * Return the simple product part of the configurable product with the selected attributes
     * @param $quoteItem
     */
    public function getSimpleProductByAttributes($configurableProduct, $attributes = array()) 
    {
        // get the necessary model
        $configurableHelper = Mage::getModel('catalog/product_type_configurable')->setProduct($configurableProduct);

        $_subproducts = $configurableHelper
            ->getUsedProductCollection()
            ->addFilterByRequiredOptions(); // the biggest speedbump


        foreach ($attributes as $attributeCode => $attributeValue) {
            $_subproducts->addAttributeToFilter($attributeCode, $attributeValue);
        }

        return $_subproducts->getFirstItem();
    }

    /**
     * If a quote item contains a configurable product, return the simple product underneath it
     * @param $quoteItem
     */
    public function getSimpleProductFromConfigurableQuoteItem(Mage_Sales_Model_Quote_Item $quoteItem) 
    {
        $attributes = array();

        if($quoteItem->getProductType() == 'configurable') {
            // get the necessary model
            $configurableHelper = Mage::getModel('catalog/product_type_configurable')->setProduct($quoteItem->getProduct());

            foreach ($quoteItem->getBuyRequest()->getSuperAttribute() as $attributeId => $value) {
                foreach ($configurableHelper->getConfigurableAttributes() as $attribute) {
                    $productAttribute = $attribute->getProductAttribute();

                    if ($productAttribute->getAttributeId() == $attributeId) {
                        $attributes[$productAttribute->getAttributeCode()] = $value;
                    }
                }
            }

            $_subproducts = $configurableHelper
                ->getUsedProductCollection()
                ->addFilterByRequiredOptions(); // the biggest speedbump


            foreach ($attributes as $attributeCode => $attributeValue) {
                $_subproducts->addAttributeToFilter($attributeCode, $attributeValue);
            }

            return $_subproducts->getFirstItem();
        }

        return $quoteItem->getProduct();
    }

    public function getConfigurableProductCombinations($_selection)
    {

        if($_selection->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
            return array();
        }

        $pr = Mage::getModel('catalog/product')->load($_selection->getProductId());

        $collection = $pr->getTypeInstance(true)->getUsedProductCollection($pr)
            ->addFilterByRequiredOptions();

        $options = array();

        $hideOos = Mage::getStoreConfig('configurablebundle/configurable/remove_oos_products', Mage::app()->getStore()->getId());

        foreach ($_selection->getTypeInstance(true)->getUsedProducts(null, $_selection) as $product) {
            if($hideOos == true && ($product->getStockItem()->getIsInStock() == false || $product->getStockItem()->getQty() <= 0)) {
                continue;
            }

            $found = false;
            foreach($collection as $collectionProduct) {
                if($collectionProduct->getId() == $product->getId()) {
                    $found = true;
                }
            }

            if($found == true) {
                $productData = array();

                foreach ($pr->getTypeInstance(true)->getConfigurableAttributes($pr) as $attribute) {
                    $productAttribute = $attribute->getProductAttribute();

                    $attributeValue = $product->getData($productAttribute->getAttributeCode());
                    $productData[$productAttribute->getAttributeId()] = $attributeValue;
                }

                $options[$product->getId()] = $productData;
            }
        }

        return $options;
    }
}