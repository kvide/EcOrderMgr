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

use EcommerceExt\Cart;
use EcommerceExt\Shipping;
use EcommerceExt\ecomm;

class order_maker extends base_order_factory
{

    private $_this_module = NULL;

    private $_shipping_policy = null;

    public function __construct(Order $order = null)
    {
        if (! $order)
        {
            $order = new Order();
        }
        $this->set_order($order);
        $this->_this_module = \cms_utils::get_module(\MOD_ECORDERMGR);
        $this->_cart_module = ecomm::get_cart_module();
        $this->set_shipping_policy(ecomm::get_system_shipping_policy());
    }

    private function &_line_item_to_cart_item(LineItem &$line_item) // TODO: was (line_item &$line_item), why?
    {
        $obj = new Cart\cartitem($line_item->get_sku(), $line_item->get_item_id(), $line_item->get_quantity());
        $obj->set_unit_price($line_item->get_unit_price());
        $obj->set_unit_weight($line_item->get_weight());
        $obj->set_summary($line_item->get_description());
        $obj->set_source($line_item->get_source());
        $obj->set_item_total($line_item->get_master_price());
        switch ($line_item->get_item_type())
        {
            case LineItem::ITEMTYPE_PRODUCT:
                $obj->set_type($obj::TYPE_PRODUCT);
                break;

            case LineItem::ITEMTYPE_SERVICE:
                $obj->set_type($obj::TYPE_SERVICE);
                break;

            case LineItem::ITEMTYPE_DISCOUNT:
            case LineItem::ITEMTYPE_SHIPPING:
            case LineItem::ITEMTYPE_TAX:
                $obj->set_type($obj::TYPE_OTHER);
                break;
        }

        return $obj;
    }

    protected function get_cart_items(Destination $destination)
    {
        $cart_items = $this->_cart_module->GetBasketItems($destination->get_name(), $this->_uid);
        $free_items = $this->find_promotion_items($cart_items);
        if (! empty($free_items))
        {
            foreach ($free_items as $free_cart_item)
            {
                $cart_items[] = $free_cart_item;
            }
        }

        return $cart_items;
    }

    protected function _get_line_items(Destination &$destination)
    {
        $cart_items = $this->get_cart_items($destination);

        // talk to promotion module and see if there are any free items.
        if ($cart_items)
        {
            $line_items = $this->find_promotion_items($cart_items);
            // create line items for the cart items.
            foreach ($cart_items as $one)
            {
                $tmp = $this->to_line_item($one);
                if (is_object($tmp))
                {
                    $line_items[] = $tmp;
                }
            }
            return $line_items;
        }
    }

    protected function _adjust_line_items_for_discounts($uid, &$items)
    {
        if (! is_array($items) || count($items) == 0)
        {
            return FALSE;
        }

        $new_items = array();
        $params = array();
        $params['uid'] = $uid;
        $params['items'] = $items;
        $params['new_items'] = &$new_items;
        \CMSMS\HookManager::do_hook('OrderMgr::CalculateDiscounts', $params);

        if (isset($params['new_items']) && is_array($params['new_items']) && count($params['new_items']))
        {
            $items = $params['new_items'];
        }

        // this stuff is deprecated... make promotions listen to the event.
        $promotionsmodule = ecomm::get_promotions_module();
        if (! $promotionsmodule)
        {
            return FALSE;
        }

        // calculate a subtotal, and a weight
        $subtotal = 0;
        $weight = 0;
        for ($i = 0; $i < count($items); $i ++)
        {
            $item = $items[$i];
            $subtotal += $item->get_quantity() * $item->get_unit_price();
            $weight += $item->get_quantity() * $item->get_weight();
        }
        $res = $promotionsmodule->FindPromotions($uid, $items, $subtotal, $weight);
        if ($res)
        {
            // replace items with adjusted ones.
            $items = $res;
            return TRUE;
        }

        return FALSE;
    }

    public function set_shipping_policy(Shipping\shipping_policy $policy)
    {
        $this->_shipping_policy = $policy;
    }

    public function set_order_notes($notes)
    {
        $this->_order->set_order_notes(trim($notes));
    }

    public function set_shipping_address($dest_idx, \xt_address $addr)
    {
        $dest = $this->_order->get_destination($dest_idx);
        $dest->set_shipping_address($addr);
    }

    public function set_shipping_pickup($dest_idx, $flag)
    {
        $dest = $this->_order->get_destination($dest_idx);
        $dest->set_pickup($flag);
    }

    public function set_shipping_message($dest_idx, $msg)
    {
        $dest = $this->_order->get_destination($dest_idx);
        $dest->set_message($msg);
    }

    public function set_order_extra($key, $val)
    {
        $this->_order->set_extra($key, $val);
    }

    public function supports_different_shipping_locations()
    {
        return TRUE;
    }

    public function validate_addresses()
    {
        $errors = null;
        if (! $this->_order->get_billing()->is_valid())
        {
            $errors[] = $this->_this_module->Lang('err_invalid_billing_address');
        }

        $policy = ecomm::get_system_shipping_policy();
        for ($i = 0; $i < $this->_order->count_destinations(); $i ++)
        {
            $dest = $this->_order->get_destination($i);

            $addr = $dest->get_shipping_address();
            if (! $addr->firstname || ! $addr->address1 || ! $addr->city)
            {
                $addr = $this->_order->get_billing();
                $dest->set_shipping_address($addr);
            }
            if (! $addr->is_valid())
            {
                $errors[] = $this->_this_module->Lang('err_invalid_shipping_address');
                break;
            }
            else
            {
                if ($policy && ! $policy->ships_to($addr))
                {
                    $errors[] = $this->_this_module->Lang('err_policy_shipfrom', $dest->get_name());
                }
            }
        }

        return $errors;
    }

    protected function get_raw_lineitems(Destination $destination)
    {
        // returns the raw product or service line items, not the meta line items like taxes, shipping, handling
        $out = null;
        $line_items = $destination->get_items();
        foreach ($line_items as $one)
        {
            if ($one->get_extra('__meta__'))
            {
                continue;
            }
            $out[] = $one;
        }

        return $out;
    }

    public function adjust_for_shipping()
    {
        // this method adjusts the order significantly
        // calculates packing lists for each destination
        // calculates shipping and handling costs for each destination
        // calculates calculates taxes for each destination
        $order = &$this->_order;
        $billing_addr = $order->get_billing();

        // handling is calculated after shipping, but the handling module does not need to
        // include shipping costs in any calculations if it does not want to.
        $handling_line_item = null;
        if ($this->_handling_module && $this->_handling_module->IsConfigured())
        {
            $items = $this->get_all_cartitems();
            $tmp = $this->_handling_module->calculate_handling($items);
            if (is_array($tmp) && count($tmp) == 2 && (float) $tmp[0] > 0)
            {
                $handling_line_item = new LineItem();
                $desc = $tmp[1];
                if (! $tmp)
                {
                    $desc = $this->Lang('handling');
                }
                $handling_line_item->set_description($desc);
                $handling_line_item->set_quantity(1);
                $handling_line_item->set_unit_price((float) $tmp[0]);
                $handling_line_item->set_item_type(LineItem::ITEMTYPE_SERVICE);
                $handling_line_item->set_extra('__meta__', 1);
            }
        }

        $numd = $order->count_destinations();
        for ($d = 0; $d < $numd; $d ++)
        {
            // for each destination
            $destination = $order->get_destination($d);
            $line_items = $this->get_raw_lineitems($destination);
            $cart_items = $this->get_cart_items($destination);

            // generate a packing list using a packaging module, or build a dummy one.
            $packing_list = null;
            if ($this->_packaging_module)
            {
                $packing_list = $this->_packaging_module->get_packing_list($destination->get_shipping_address(), $cart_items);
            }
            else
            {
                // create a packing list with all of the cart items, and the shipping address
                $packing_list = new Shipping\packing_list($destination->get_shipping_address(), $cart_items);
            }
            if ($packing_list)
            {
                $destination->set_packing_list($packing_list);
            }

            if ($packing_list && $this->_shipping_module && ! $destination->get_pickup())
            {
                $shipping_cost = $this->_shipping_module->calculate_shipping($packing_list);
                if (is_float($shipping_cost))
                {
                    // deprecated
                    $line_item = new LineItem();
                    $line_item->set_description($this->_this_module->Lang('shipping'));
                    $line_item->set_quantity(1);
                    $line_item->set_unit_price($shipping_cost);
                    $line_item->set_item_type(LineItem::ITEMTYPE_SHIPPING);
                    $line_item->set_extra('__meta__', 1);
                    $line_items[] = $line_item;
                }
                else if ($shipping_cost instanceof Shipping\shipping_estimate)
                {
                    $line_item = new LineItem();
                    $line_item->set_description($shipping_cost->description);
                    $line_item->set_unit_price($shipping_cost->price);
                    $line_item->set_item_type(LineItem::ITEMTYPE_SHIPPING);
                    $line_item->set_quantity(1);
                    $line_item->set_extra('__meta__', 1);
                    $line_items[] = $line_item;
                }
                else
                {
                    die('uhoh - shpping module problem at ' . __FILE__ . '::' . __LINE__);
                }
            }

            // and add in handling if any, but only once per order.
            if ($handling_line_item)
            {
                $line_items[] = $handling_line_item;
                $handling_line_item = null;
            }

            // and calculate taxes, returns array of line items.
            $tmp = $this->calculate_taxes($destination, $line_items);
            if (is_array($tmp))
            {
                foreach ($tmp as &$one)
                {
                    $line_items[] = $one;
                }
            }

            // now do a merge
            $destination->set_items($line_items);
        }

        return $order;
    }

    public function get_basic_order()
    {
        // todo, do checks...
        $exists = FALSE;

        if ($this->_cart_module->GetNumItems() == 0)
        {
            throw new \CmsException($this->_this_module->Lang("no_items_in_cart"));
        }
        if (! $this->_shipping_policy)
        {
            throw new \RuntimeException($this->_this_module->Lang('err_noshippingpolicy'));
        }

        $order = &$this->_order;
        $order->set_feu_user($this->_uid);
        $order->set_status(\EcommerceExt\ORDERSTATUS_PROPOSED);

        // set billing address into the order (only one of these)
        $billing_addr = $this->_billing_address;
        if (! $billing_addr)
        {
            $billing_addr = $order->get_billing();
            if (! $billing_addr)
            {
                $billing_addr = new \xt_address();
            }
        }
        $order->set_billing($billing_addr);

        // now get the stuff out of the cart into the order.
        if ($this->_cart_module)
        {
            $basket_names = $this->_cart_module->GetBasketNames($this->_uid);
            foreach ($basket_names as $one_basket_name)
            {
                $destination = $order->get_destination_by_name($one_basket_name);
                $added = false;
                if (! is_object($destination))
                {
                    $destination = new Destination();
                    $added = true;
                }
                $destination->remove_all_items();

                $basket_details = $this->_cart_module->GetBasketDetails($one_basket_name, $this->_uid);
                $cart_items = $this->_cart_module->GetBasketItems($one_basket_name, $this->_uid);
                if (! $cart_items)
                {
                    continue;
                }
                $destination->set_name($one_basket_name);
                // if( isset($basket_details['cart_name']) && $basket_details['cart_name'] ) $destination->set_name($basket_details['cart_name']);
                // $shipping_addr = new OrderMgr\Address();
                $shipping_addr = $destination->get_shipping_address();

                // if we have something from the basket/cart... it always takes precedence.
                // otherwise... we see if its already in the shipping object
                // if it is not, we copy the billing address.
                $tmp_addr = new Address();
                $tmp_addr->from_array($basket_details, 'dest_');
                if ($tmp_addr->is_valid())
                {
                    $shipping_addr = $tmp_addr;
                }
                else if (! $shipping_addr || ! $shipping_addr->is_valid())
                {
                    // no address for this shipping, so lets use the billing address
                    $shipping_addr = clone $billing_addr;
                }
                if ($shipping_addr)
                {
                    $destination->set_shipping_address($shipping_addr);
                }

                // set the source where stuff is coming from for this destination/shipping/sub-order
                $destination->set_source_address(ecomm::get_company_address());

                if (isset($basket_details['dest_message']))
                {
                    $msg = \xt_utils::clean_input_html($basket_details['dest_message']);
                    $destination->set_message($msg);
                }

                $line_items = $this->_get_line_items($destination);
                if (! $line_items)
                {
                    continue;
                }
                for ($i = 0; $i < count($line_items); $i ++)
                {
                    $destination->add_item($line_items[$i]);
                }

                // will we allow local pickup
                // only if shipping policy says we can and we only have one basket.
                $flag = false;
                if ($this->_shipping_policy->can_pickup && $this->_cart_module->GetNumBaskets() == 1)
                {
                    $flag = true;
                }
                $destination->allow_pickup($flag);

                // and add this shipment to the order.
                if ($added)
                {
                    $order->add_shipping($destination);
                }
            }
        }

        return $order;
    }

} // end of class

// EOF
?>
