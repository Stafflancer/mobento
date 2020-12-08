<?php

class Wizkunde_ConfigurableBundle_Model_Bundle_Product_Price extends Mage_Bundle_Model_Product_Price {

    public function getSelectionFinalTotalPrice($bundleProduct, $selectionProduct, $bundleQty, $selectionQty,
                                                $multiplyQty = true, $takeTierPrice = true)
    {
        if (is_null($selectionQty)) {
            $selectionQty = $selectionProduct->getSelectionQty();
        }

        $price = $selectionProduct->getPriceModel()->getFinalPrice($selectionQty, $selectionProduct);

        /**
         * Set the price properly for products if they are configurable
         * Includes tier, group and customer specific
         */
        if ($bundleProduct->hasCustomOptions()) {
            $buyRequest = $bundleProduct->getCustomOption('info_buyRequest');
            $buyRequestData = unserialize($buyRequest->getValue());

            if($buyRequest != null) {
                if($selectionProduct->getTypeId() == 'configurable') {
                    if(isset($buyRequestData['super_attribute']) && isset($buyRequestData['super_attribute'][$selectionProduct->getEntityId()])) {
                        $simpleProduct = Mage::helper('configurablebundle/bundle')->getSimpleProductByAttributes(
                            $selectionProduct,
                            $buyRequestData['super_attribute'][$selectionProduct->getEntityId()]
                        );

                        $loadedProduct = Mage::getModel('catalog/product')->load($simpleProduct->getId());
                        $price = $loadedProduct->getPriceModel()->getFinalPrice($selectionQty, $loadedProduct);
                    }
                }
            }
        }

        if ($bundleProduct->getPriceType() != self::PRICE_TYPE_DYNAMIC) {
            if ($selectionProduct->getSelectionPriceType()) { // percent
                $product = clone $bundleProduct;
                $product->setFinalPrice($this->getPrice($product));
                Mage::dispatchEvent(
                    'catalog_product_get_final_price',
                    array('product' => $product, 'qty' => $bundleQty)
                );
                $price = $product->getData('final_price') * ($selectionProduct->getSelectionPriceValue() / 100);

            } else { // fixed
                $price = $selectionProduct->getSelectionPriceValue();
            }
        }

        $customOptions = $bundleProduct->getCustomOption('bundle_simple_custom_options');
        $basePrice = $price;
        if($customOptions){
            $custom_options = unserialize($customOptions->getValue());
        }
        if(!empty($custom_options)){
            foreach($custom_options as $id => $info){
                if($selectionProduct->getEntityId() == $id){
                    foreach($custom_options[$id]['options'] as $optionId=>$value){
                        $s_product = Mage::getModel('catalog/product')->load($selectionProduct->getEntityId());
                        $s_option = $s_product->getOptionById($optionId);                       
                        $confItemOption = Mage::getModel('catalog/product_configuration_item_option')
                            ->addData(array(
                                'product_id'=> $s_product->getId(),
                                'product'   => $s_product,
                                'code'      => 'option_'.$optionId,
                                'value'     => is_array($value)? implode(',', $value) : $value,
                            ));
                        $group = $s_option->groupFactory($s_option->getType())
                            ->setOption($s_option)
                            ->setConfigurationItemOption($confItemOption);
                        $price += $group->getOptionPrice($confItemOption->getValue(), $basePrice);
                    }
                }
            }
        }
        if ($multiplyQty) {
            $price *= $selectionQty;
        }

        Mage::dispatchEvent(
            'catalog_product_get_bundle_discount_price',
            array('product' => $bundleProduct, 'qty' => $bundleQty, 'price' => $price)
        );

        return min($price,
            $this->_applyGroupPrice($bundleProduct, $bundleProduct->getFinalPrice()),
            $this->_applyTierPrice($bundleProduct, $bundleQty, $bundleProduct->getFinalPrice()),
            $this->_applySpecialPrice($bundleProduct, $bundleProduct->getFinalPrice())
        );
    }
}