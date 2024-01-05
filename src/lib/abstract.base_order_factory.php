<?php

# BEGIN_LICENSE
# -------------------------------------------------------------------------
# Module: EcOrderMgr (c) 2023 by CMS Made Simple Foundation
#
# An addon module for CMS Made Simple to allow users to create, manage
# and display orders made through the Ecommerce extensions.
# -------------------------------------------------------------------------
# A fork of:
#
# Module: Orders (c) 2008-2019 by Robert Campbell
# (calguy1000@cmsmadesimple.org)
#
# -------------------------------------------------------------------------
#
# CMSMS - CMS Made Simple is (c) 2006 - 2023 by CMS Made Simple Foundation
# CMSMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# Visit the CMSMS Homepage at: http://www.cmsmadesimple.org
#
# -------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple. You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin
# section that the site was built with CMS Made simple.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
# -------------------------------------------------------------------------
# END_LICENSE

namespace EcOrderMgr;

use EcommerceExt\Tax;
use EcommerceExt\Cart;
use EcommerceExt\Shipping;
use EcommerceExt\Handling;
use EcommerceExt\Packaging;
use EcommerceExt\ecomm;

abstract class base_order_factory implements order_factory
{

    protected $_order;
    protected $_cart_module;
    protected $_shipping_module;
    protected $_handling_module;
    protected $_tax_module;
    protected $_packing_module;
    protected $_billing_address;
    protected $_uid;

    public function set_order(Order $order)
    {
        $this->_order = $order;
    }

    public function supports_multiple_vendors()
    {
        return FALSE;
    }

    public function set_billing_address(\xt_address $address)
    {
        $this->_billing_address = $address;
        $this->_order->set_billing($address);
    }

    public function set_cart_module(Cart\shopping_cart_mgr $module)
    {
        if ($module->SupportsMultipleBaskets() && $this->supports_multiple_vendors())
        {
            throw new \RuntimeException('Sorry, the cart module selected cannot be used in this configuration as'
                                            . ' the order maker can support orders from multiple vendors.');
        }
        $this->_cart_module = $module;
    }

    public function set_shipping_module(Shipping\shipping_assistant $module = null)
    {
        $this->_shipping_module = $module;
    }

    public function set_tax_module(Tax\tax_calculator $module = null)
    {
        $this->_tax_module = $module;
    }

    public function set_handling_module(Handling\handling_calculator $module = null)
    {
        $this->_handling_module = $module;
    }

    public function set_packaging_module(Packaging\packaging_calculator $module = null)
    {
        $this->_packaging_module = $module;
    }

    public function set_user_id($uid)
    {
        $this->_uid = (int) $uid;
    }

    protected function to_cart_item(LineItem $line_item)
    {
        $new_cart_item = new Cart\cartitem($line_item->get_sku(), $line_item->get_item_id(), $line_item->get_quantity(),
                                            $line_item->get_source());
        switch ($line_item->get_item_type())
        {
            case $line_item::ITEMTYPE_PRODUCT:
                $new_cart_item->set_type($new_cart_item::TYPE_PRODUCT);
                break;
            case $line_item::ITEMTYPE_SERVICE:
                $new_cart_item->set_type($new_cart_item::TYPE_SERVICE);
                break;
            case $line_item::ITEMTYPE_DISCOUNT:
                $new_cart_item->set_type($new_cart_item::TYPE_DISCOUNT);
                break;
            case $line_item::ITEMTYPE_SHIPPING:
                $new_cart_item->set_type($new_cart_item::TYPE_SHIPPING);
                break;
            case $line_item::ITEMTYPE_TAX:
                $new_cart_item->set_type($new_cart_item::TYPE_TAX);
                break;
        }
        $new_cart_item->set_unit_price($line_item->get_unit_price());
        $new_cart_item->set_unit_weight($line_item->get_unit_weight());
        $new_cart_item->set_unit_discount($line_item->get_unit_discount());
        $new_cart_item->set_summary($line_item->get_description());
        if ($line_item->get_master_price() > 0)
            $new_cart_item->set_item_total($line_item->get_master_price());
        $new_cart_item->set_digital($line_item->is_digital());
        if (($val = $line_item->get_extra('promotion_id')))
        {
            $new_cart_item->set_promo($val);
        }

        // todo, handle promotion
        return $new_cart_item;
    }

    protected function to_line_item(Cart\cartitem $basket_item)
    {
        $new_line_item = null;
        // pending/proposed/estimated items don't get handled here.
        if ($basket_item->get_pending() || $basket_item->is_estimated())
        {
            return $new_line_item;
        }

        $new_line_item = new LineItem();
        $cart_type = $basket_item->get_type();
        $new_type = LineItem::ITEMTYPE_PRODUCT;
        switch ($cart_type)
        {
            case Cart\cartitem::TYPE_SERVICE:
                $new_type = LineItem::ITEMTYPE_SERVICE;
                break;
            case Cart\cartitem::TYPE_SHIPPING:
                $new_type = LineItem::ITEMTYPE_SHIPPING;
                break;
            case Cart\cartitem::TYPE_TAXES:
                $new_type = LineItem::ITEMTYPE_TAXES;
                break;
            case Cart\cartitem::TYPE_DISCOUNT:
                $new_type = LineItem::ITEMTYPE_DISCOUNT;
                break;
            case Cart\cartitem::TYPE_OTHER:
                $new_type = LineItem::ITEMTYPE_OTHER;
                break;
            case Cart\cartitem::TYPE_PRODUCT:
            default:
                $new_type = LineItem::ITEMTYPE_PRODUCT;
                break;
        }

        $new_line_item->set_item_type($new_type);
        $new_line_item->set_item_id($basket_item->get_product_id());
        $new_line_item->set_quantity($basket_item->get_quantity());
        $new_line_item->set_unit_price($basket_item->get_unit_price());
        $new_line_item->set_unit_weight($basket_item->get_unit_weight());
        $new_line_item->set_description($basket_item->get_summary());
        $new_line_item->set_unit_discount($basket_item->get_unit_discount());
        $new_line_item->set_status(\EcommerceExt\ITEMSTATUS_NOTSHIPPED);
        $new_line_item->set_sku($basket_item->get_sku());
        $new_line_item->set_source($basket_item->get_source());
        $new_line_item->set_master_price($basket_item->get_item_total());
        $new_line_item->set_digital($basket_item->is_digital());
        $t_promo = $basket_item->get_promo();
        if ($t_promo > 0)
        {
            $new_line_item->set_extra('promotion_id', $t_promo);
        }

        $tmp = $basket_item->get_subscription();
        if ($tmp)
        {
            $new_line_item->set_subscr_payperiod($tmp->get_payperiod());
            $new_line_item->set_subscr_delperiod($tmp->get_deliveryperiod());
            $expires = $tmp->get_expiry();
            if ($expires > 0)
            {
                $t2 = strtotime(sprintf('+%d months', $expires));
                if ($t2)
                {
                    $t2 = \xt_date_utils::ts_to_dbformat($t2);
                    $new_line_item->set_subscr_expires($t2);
                }
            }
        }

        return $new_line_item;
    }

    protected function find_promotion_items(array $cart_items)
    {
        // given an array of cart items, pass them through a promotions tester
        // and see if there are any new items.
        // return an array of cart items that is separate from the input cart items.
        // this should prolly belong in the Promotions module or EcommerceExt

        // see if we're gonna discount anything here.
        $offers = null;
        $tester = ecomm::get_promotions_tester();
        if (! $tester)
        {
            return;
        }

        $tester->set_promo_type($tester::TYPE_CHECKOUT);
        $tester->set_cart($cart_items);
        $offers = $tester->find_all_cart_matches();
        if (! is_array($offers) || ! count($offers))
        {
            return;
        }

        // get the supplier module name... right now we make an assumption that you can only check out from
        // one supplier at a time... in order to fix that we would need to have to have promotions with
        // supplier information.
        $supplier_mod = $cart_items[0]->get_source();

        $out = null;
        foreach ($offers as $offer)
        {
            $free_cart_item = null;
            if (is_object($offer))
            {
                switch ($offer->get_type())
                {
                    case $offer::OFFER_PRODUCTID:
                        // add a new product (by id) free, to the end of the cart
                        $free_product = ecomm::get_product_info($supplier_mod, $offer->get_val());
                        $free_cart_item = new Cart\cartitem($free_product->get_sku(), $free_product->get_product_id(),
                                                                1, $supplier_mod);
                        $free_cart_item->set_unit_weight($free_product->get_weight());
                        $free_cart_item->set_promo($offer->get_promo());
                        $free_cart_item->set_unit_price($free_product->get_price());
                        $free_cart_item->set_unit_discount($free_product->get_price() * - 1);
                        $free_cart_item->set_summary($free_product->get_name());
                        $free_cart_item->set_allow_remove(FALSE);
                        $free_cart_item->set_allow_quantity_adjust(FALSE);
                        $dims = $free_product->get_dimensions();
                        if ($dims)
                        {
                            $free_cart_item->set_dimensions($dims[0], $dims[1], $dims[2]);
                        }
                        break;

                    case $offer::OFFER_PRODUCTSKU:
                        $free_product = ecomm::get_product_by_sku($supplier_mod, $offer->get_val());
                        $base_price = $free_product->get_price();
                        $free_cart_item = new Cart\cartitem($free_product->get_sku(), $free_product->get_product_id(),
                                                                1, $supplier_mod);
                        $free_cart_item->set_unit_weight($free_product->get_weight());
                        $free_cart_item->set_promo($offer->get_promo());
                        $free_cart_item->set_unit_price($base_price);
                        $free_cart_item->set_unit_discount($base_price * - 1);
                        $free_cart_item->set_summary($free_product->get_name());
                        $free_cart_item->set_allow_remove(FALSE);
                        $free_cart_item->set_allow_quantity_adjust(FALSE);
                        $dims = $free_product->get_dimensions();
                        if ($dims)
                        {
                            $free_cart_item->set_dimensions($dims[0], $dims[1], $dims[2]);
                        }
                        $opt = $free_product->get_option_by_sku($offer->get_val());
                        if (is_object($opt))
                        {
                            $free_cart_item->set_sku($opt->sku);
                            $base_price = $opt->parse_adjustment($free_product->get_price());
                            $free_cart_item->set_summary($free_product->get_name() . ': ' . $opt->text);
                        }
                        $free_cart_item->set_sku($offer->get_val());
                        break;

                    case $offer::OFFER_DISCOUNT:
                        if (($idx = $offer->get_cart_idx()) > - 1)
                        {
                            // we're discounting a certain cart index
                            if ($idx >= 0 && $idx < count($cart_items))
                            {
                                $item = &$cart_items[$idx];
                                if ($item->get_unit_discount() == '' && $item->get_parent() == - 1)
                                {
                                    $val = abs($offer->get_discount_amount());
                                    $val = min($val, $item->get_unit_price());
                                    $item->set_unit_discount($val * - 1);
                                }
                            }
                        }
                        else
                        {
                            // add a new discount line item.
                            $free_cart_item = new Cart\cartitem('', '', 1, 'Promotions');
                            $free_cart_item->set_promo($offer->get_promo());
                            $free_cart_item->set_unit_discount($offer->get_discount_amount() * - 1);
                            $free_cart_item->set_allow_remove(FALSE);
                            $free_cart_item->set_type($free_cart_item::TYPE_DISCOUNT);
                            $free_cart_item->set_summary($offer->get_name());
                            $free_cart_item->set_allow_quantity_adjust(FALSE);
                        }
                        break;

                    case $offer::OFFER_PERCENT:
                        if (($idx = $offer->get_cart_idx()) > - 1)
                        {
                            // a percentage off a certain cart index.
                            if ($idx >= 0 && $idx < count($cart_items))
                            {
                                $item = &$cart_items[$idx];
                                if (($item->get_unit_discount() == '' && $item->get_parent() == - 1)
                                    || $offer->get_cumulative())
                                {
                                    $val = abs($offer->get_val());
                                    $val = min($val, 1.0);
                                    $discount = round($item->get_unit_discount() + $item->get_unit_price()
                                                        * $val * - 1, 2);
                                    $discount = max($item->get_unit_price() * - 1, $discount);
                                    $item->set_unit_discount($discount);
                                }
                            }
                        }
                        else
                        {
                            // percentage off order total.
                            $val = abs($offer->get_val());
                            $val = min($val, 1.0);
                            $val = round($val, 2);

                            if ($offer->get_cumulative())
                            {
                                $total = 0;
                                foreach ($cart_items as &$item)
                                {
                                    if ($item->get_type() != Cart\cartitem::TYPE_DISCOUNT)
                                    {
                                        $total += $item->get_unit_price() * $item->get_quantity();
                                    }
                                }
                                // add a new discount line item.
                                $discount = $total * $val * - 1;
                                $discount = max($total * - 1, $discount);
                                if ($discount < 0)
                                {
                                    $free_cart_item = new Cart\cartitem('', '', 1, 'Promotions');
                                    $free_cart_item->set_promo($offer->get_promo());
                                    $free_cart_item->set_unit_discount($discount);
                                    $free_cart_item->set_allow_remove(FALSE);
                                    $free_cart_item->set_type($free_cart_item::TYPE_DISCOUNT);
                                    $free_cart_item->set_summary($offer->get_name());
                                    $free_cart_item->set_allow_quantity_adjust(FALSE);
                                }
                            }
                            else
                            {
                                foreach ($cart_items as &$item)
                                {
                                    if (($item->get_unit_discount() == '' && $item->get_parent() == - 1))
                                    {
                                        $discount = $item->get_unit_discount() + $item->get_unit_price() * $val * - 1;
                                        $discount = max($item->get_unit_price() * - 1, $discount);
                                        $item->set_unit_discount($discount);
                                    }
                                }
                            }
                        }
                        break;
                }
            }

            // this adds the new cart item to the list.
            // if( $free_cart_item ) $out[] = $this->to_line_item($free_cart_item);
            if ($free_cart_item)
            {
                $out[] = $free_cart_item;
            }
        }

        return $out;
    }

    protected function get_lineitem_subtotal(array $line_items)
    {
        $subtotal = 0.0;
        for ($i = 0; $i < count($line_items); $i ++)
        {
            $item = $line_items[$i];
            switch ($item->get_item_type())
            {
                case LineItem::ITEMTYPE_TAX:
                case LineItem::ITEMTYPE_SHIPPING:
                case LineItem::ITEMTYPE_DISCOUNT:
                    break;
                default:
                    $subtotal += max($item->get_quantity() * $item->get_unit_price(), $item->get_master_price());
                    break;
            }
        }
        return $subtotal;
    }

    private function _calculate_item_taxes(Tax\tax_calculator $taxmodule, LineItem $oneitem, &$cumulative_taxes)
    {
        $cart_item = $this->to_cart_item($oneitem); // works on cart items
        $tax_array = $taxmodule->calculate_taxes($cart_item);
        if ($tax_array)
        {
            foreach ($tax_array as $key => $value)
            {
                if (! isset($cumulative_taxes[$key]))
                {
                    $cumulative_taxes[$key] = 0;
                }
                $cumulative_taxes[$key] += $value;
            }
            return TRUE;
        }

        return FALSE;
    }

    protected function setup_tax_module(Destination $dest)
    {
        // Get the tax module
        $taxmodule = $this->_tax_module;
        if (! $taxmodule || ! $taxmodule->IsConfigured())
        {
            return;
        }

        // Provide billing information to the taxes module
        // for this order maker, the source address is the EcommerceExt company address
        $taxmodule->set_source_location(ecomm::get_company_address());
        $taxmodule->set_dest_location($dest->get_shipping_address());
    }

    protected function calculate_taxes(Destination $dest, $items)
    {
        // Get the tax module
        $taxmodule = $this->_tax_module;
        if (! $taxmodule || ! $taxmodule->IsConfigured())
        {
            return;
        }

        $this->setup_tax_module($dest);

        // for each item, calculate the taxes
        $results = array();
        if (is_array($items) && count($items))
        {
            foreach ($items as $oneitem)
            {
                switch ($oneitem->get_item_type())
                {
                    case LineItem::ITEMTYPE_DISCOUNT:
                    case LineItem::ITEMTYPE_SERVICE:
                        $this->_calculate_item_taxes($taxmodule, $oneitem, $results);
                        break;

                    case LineItem::ITEMTYPE_SHIPPING:
                        if (ecomm::can_tax_shipping())
                            $this->_calculate_item_taxes($taxmodule, $oneitem, $results);
                        break;

                    case LineItem::ITEMTYPE_PRODUCT:
                        $source = $oneitem->get_source();
                        $product_info = ecomm::get_product_info($source, $oneitem->get_item_id());
                        $taxable = $product_info->get_taxable();
                        if ($taxable)
                            $this->_calculate_item_taxes($taxmodule, $oneitem, $results);
                        break;

                    default:
                        break;
                }
            }
        }

        if (! is_array($results) || count($results) == 0)
        {
            return;
        }
        $line_items = array();
        foreach ($results as $key => $value)
        {
            $line_item = new LineItem();
            $line_item->set_description($key);
            $line_item->set_quantity(1);
            $line_item->set_unit_price($value);
            $line_item->set_item_type(LineItem::ITEMTYPE_TAX);
            $line_item->set_extra('__meta__', 1);
            $line_items[] = $line_item;
        }

        return $line_items;
    }

    protected function get_all_cartitems()
    {
        // get all cart items as a flat array
        // does not account for duplicated cart items in multiple baskets
        $out = null;
        $basket_names = $this->_cart_module->GetBasketNames($this->_uid);
        foreach ($basket_names as $one_basket_name)
        {
            $items = $this->_cart_module->GetBasketItems($one_basket_name);
            foreach ($items as $one)
            {
                $out[] = $one;
            }
        }

        return $out;
    }

} // end of class

?>
