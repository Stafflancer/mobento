<?php
/**
 * Monbento_FeaturedProducts extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Monbento
 * @package    Monbento_FeaturedProducts
 * @copyright  Copyright (c) 2010 Anthony Charrex
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Monbento
 * @package    Monbento_FeaturedProducts
 * @author     Anthony Charrex <anthony.charrax@gmail.com>
 */

$installer = $this;

$installer->startSetup();

$installer->removeAttribute('catalog_product', 'is_featured');

$installer->endSetup(); 