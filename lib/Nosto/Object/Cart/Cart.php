<?php
/**
 * Copyright (c) 2017, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2017 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */



/**
 * Model class containing the information about the particulars of a shopping cart.
 */
class Nosto_Object_Cart_Cart extends Nosto_AbstractObject
{
    /**
     * @var string URL for restoring cart
     */
    private $restoreCartUrl;

    /**
     * @var Nosto_Types_LineItemInterface[] the array of items in the shopping cart
     */
    private $items = array();

    /**
     * Returns the items in the shopping cart
     *
     * @return Nosto_Types_LineItemInterface[] the items in the shopping cart
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Sets the cart items in the shopping cart
     *
     * @param Nosto_Types_LineItemInterface[] $items the items of the shopping cart
     */
    public function setItems(array $items)
    {
        $this->items = array();
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    /**
     * Adds a new item to the shopping cart
     *
     * @param Nosto_Types_LineItemInterface $item the item to add to the shopping cart
     */
    public function addItem(Nosto_Types_LineItemInterface $item)
    {
        $this->items[] = $item;
    }

    /**
     * Returns the restore cart URL
     *
     * @return string
     */
    public function getRestoreCartUrl()
    {
        return $this->restoreCartUrl;
    }

    /**
     * Sets the restore cart URL
     *
     * @param string $restoreCartUrl
     */
    public function setRestoreCartUrl($restoreCartUrl)
    {
        $this->restoreCartUrl = $restoreCartUrl;
    }
}